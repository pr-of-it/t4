<?php

namespace T4\Http;

use T4\Core\Exception;

class Uploader
{

    protected $formFieldName = '';
    protected $uploadPath;

    public function __construct($name='')
    {
        $this->formFieldName = $name;
    }

    public function setPath($path)
    {
        $this->uploadPath = $path;
    }

    public function __invoke($name='')
    {

        if (empty($this->formFieldName) && !empty($name))
            $this->formFieldName = $name;

        if (empty($this->formFieldName))
            throw new Exception('Empty form field name for file upload');

        if (empty($this->uploadPath) && !is_readable(ROOT_PATH_PUBLIC . DS. $this->uploadPath) && !is_dir(ROOT_PATH_PUBLIC . DS. $this->uploadPath))
            throw new Exception('Invalid upload path');

        if (empty($_FILES[$this->formFieldName]) || 0==$_FILES[$this->formFieldName]['size'])
            throw new Exception('File \'' . $this->formFieldName . '\' is not uploaded');

        if (\UPLOAD_ERR_OK != $_FILES[$this->formFieldName]['error'])
            throw new Exception('Upload error while uploading file \'' . $this->formFieldName . '\': '.$_FILES[$this->formFieldName]['error']);

        $file = ROOT_PATH_PUBLIC . DS. $this->uploadPath . '/' . basename($_FILES[$this->formFieldName]['name']);
        $ret = str_replace(DS, '/', $this->uploadPath) . '/' . basename($_FILES[$this->formFieldName]['name']);

        if (!move_uploaded_file($_FILES[$this->formFieldName]['tmp_name'], $file)) {
            throw new Exception('Save uploaded file error');
        }

        return $ret;

    }

} 