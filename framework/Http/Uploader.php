<?php

namespace T4\Http;

use T4\Core\Exception;
use T4\Mvc\Application;

class Uploader
{

    protected $formFieldName = '';
    protected $uploadPath;

    public function __construct($name = '')
    {
        $this->formFieldName = $name;
    }

    public function setPath($path)
    {
        $this->uploadPath = $path;
    }

    public function isUploaded($name = '')
    {
        if (!empty($this->formFieldName) && empty($name))
            $name = $this->formFieldName;

        if (empty($name))
            throw new Exception('Empty form field name for file upload');

        $request = Application::getInstance()->request;
        if (!$request->isUploaded($name))
            return false;

        if (
            !$request->isUploadedArray($name)
            && (
                0 == $request->files->{$name}->size
                ||
                \UPLOAD_ERR_OK != $request->files->{$name}->error
            )
        ) {
            return false;
        }

        return true;
    }

    public function __invoke($name = '')
    {

        if (empty($this->formFieldName) && !empty($name))
            $this->formFieldName = $name;

        if (empty($this->formFieldName))
            throw new Exception('Empty form field name for file upload');

        if (empty($this->uploadPath))
            throw new Exception('Invalid upload path');

        $realUploadPath = \T4\Fs\Helpers::getRealPath($this->uploadPath);
        if (!is_dir($realUploadPath)) {
            try {
                \T4\Fs\Helpers::mkDir($realUploadPath);
            } catch (\T4\Fs\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        $request = Application::getInstance()->request;
        if (!$request->isUploaded($this->formFieldName))
            throw new Exception('File for \'' . $this->formFieldName . '\' is not uploaded');

        if (!$this->isUploaded($this->formFieldName))
            throw new Exception('Error while uploading file \'' . $this->formFieldName . '\': ' . $request->files->{$this->formFieldName}->error);

        if ($request->isUploadedArray($this->formFieldName)) {

            $ret = [];
            foreach ($request->files->{$this->formFieldName} as $n => $file) {
                $uploadedFileName = $this->suggestUploadedFileName($realUploadPath, $file->name);
                if (move_uploaded_file($file->tmp_name, $realUploadPath . DS . $uploadedFileName)) {
                    $ret[$n] = $this->uploadPath . '/' . $uploadedFileName;
                } else {
                    $ret[$n] = false;
                }
            }
            return $ret;

        } else {

            $file = $request->files->{$this->formFieldName};
            $uploadedFileName = $this->suggestUploadedFileName($realUploadPath, $file->name);
            if (move_uploaded_file($file->tmp_name, $realUploadPath . DS . $uploadedFileName)) {
                return $this->uploadPath . '/' . $uploadedFileName;
            } else {
                return false;
            }

        }
    }

    protected function suggestUploadedFileName($path, $name)
    {
        if (!file_exists($path . DS . $name))
            return strtolower($name);

        $filename = pathinfo($name, \PATHINFO_FILENAME);
        $extension = pathinfo($name, \PATHINFO_EXTENSION);
        preg_match('~(.*?)(_(\d+))?$~', $filename, $m);
        $i = isset($m[3]) ? (int)$m[3] + 1 : 1;

        while (file_exists($path . DS . ($file = $m[1] . '_' . $i . '.' . $extension)))
            $i++;

        return strtolower($file);
    }

}