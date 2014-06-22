<?php

namespace T4\Http;

use T4\Core\Exception;
use T4\Fs\Helpers;

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

    public function __invoke($name = '')
    {
        if (empty($this->uploadPath))
            throw new Exception('Invalid upload path');

        $realUploadPath = ROOT_PATH_PUBLIC . str_replace('/', DS, $this->uploadPath);
        if (!is_dir($realUploadPath)) {
            try {
                Helpers::mkDir($realUploadPath);
            } catch (\T4\Fs\Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        if (empty($this->formFieldName) && !empty($name))
            $this->formFieldName = $name;

        if (empty($this->formFieldName))
            throw new Exception('Empty form field name for file upload');

        if (empty($_FILES[$this->formFieldName]) || 0 == $_FILES[$this->formFieldName]['size'])
            throw new Exception('File \'' . $this->formFieldName . '\' is not uploaded');

        if (\UPLOAD_ERR_OK != $_FILES[$this->formFieldName]['error'])
            throw new Exception('Upload error while uploading file \'' . $this->formFieldName . '\': ' . $_FILES[$this->formFieldName]['error']);

        $uploadedFileName = basename($_FILES[$this->formFieldName]['name']);
        while ( file_exists($realUploadPath . DS . $uploadedFileName) ) {
            $uploadedFileName = pathinfo($uploadedFileName, PATHINFO_FILENAME) . '_.' . pathinfo($uploadedFileName, PATHINFO_EXTENSION);
        };
        $ret = str_replace(DS, '/', $this->uploadPath) . '/' . $uploadedFileName;

        if (!move_uploaded_file($_FILES[$this->formFieldName]['tmp_name'], $realUploadPath . DS . $uploadedFileName)) {
            throw new Exception('Save uploaded file error');
        }

        return $ret;

    }

}