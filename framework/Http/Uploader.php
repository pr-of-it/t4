<?php

namespace T4\Http;

use T4\Core\Exception;

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

    public function isUploaded($name='')
    {
        if (!empty($this->formFieldName) && empty($name))
            $name = $this->formFieldName;

        if (empty($name))
            throw new Exception('Empty form field name for file upload');

        if (empty($_FILES[$name]) || 0 == $_FILES[$name]['size'])
            return false;

        if (\UPLOAD_ERR_OK != $_FILES[$this->formFieldName]['error'])
            return false;

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

        $realUploadPath = ROOT_PATH_PUBLIC . str_replace('/', DS, $this->uploadPath);
        if (!is_dir($realUploadPath)) {
            try {
                \T4\Fs\Helpers::mkDir($realUploadPath);
            } catch (\T4\Fs\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        if (empty($_FILES[$this->formFieldName]) || 0 == $_FILES[$this->formFieldName]['size'])
            throw new Exception('File for \'' . $this->formFieldName . '\' is not uploaded');

        if (\UPLOAD_ERR_OK != $_FILES[$this->formFieldName]['error'])
            throw new Exception('Upload error while uploading file \'' . $this->formFieldName . '\': ' . $_FILES[$this->formFieldName]['error']);

        $uploadedFileName = $this->suggestUploadedFileName($realUploadPath, $_FILES[$this->formFieldName]['name']);

        if (!move_uploaded_file($_FILES[$this->formFieldName]['tmp_name'], $realUploadPath . DS . $uploadedFileName)) {
            throw new Exception('Save uploaded file error');
        }

        return $this->uploadPath . '/' . $uploadedFileName;

    }

    protected function suggestUploadedFileName($path, $name)
    {
        if (!file_exists($path.DS.$name))
            return strtolower($name);

        $filename = pathinfo($name, \PATHINFO_FILENAME);
        $extension = pathinfo($name, \PATHINFO_EXTENSION);
        preg_match('~(.*?)(_(\d+))?$~', $filename, $m);
        $i = isset($m[3]) ? (int)$m[3]+1 : 1;

        while (file_exists($path.DS.($file = $m[1] . '_' . $i . '.' . $extension)))
            $i++;

        return strtolower($file);

    }

}