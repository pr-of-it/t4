<?php

namespace T4\Http;

use T4\Core\Std;


/**
 * Class Request
 * @package T4\Http
 * @property string $path
 * @property \T4\Core\Std $get
 * @property \T4\Core\Std $post
 * @property \T4\Core\Std $files
 * @property \T4\Core\Std $headers
 */
class Request
    extends Std
{

    public function __construct()
    {
        $this->get = new Std($_GET);

        $this->post = new Std($_POST);

        $this->files = new Std();
        if (!empty($_FILES)) {
            foreach ($_FILES as $name => $data) {
                if (is_array($data['error'])) {
                    $file = [];
                    foreach ($data['error'] as $n => $error) {
                        $file[$n] = new Std();
                        $file[$n]->error = $error;
                        $file[$n]->name = $data['name'][$n];
                        $file[$n]->tmp_name = $data['tmp_name'][$n];
                        $file[$n]->size = $data['size'][$n];
                    }
                    $this->files->$name = $file;
                } else {
                    $this->files->$name = new Std($data);
                }
            }
        }

        $this->headers = new Std($this->getHttpHeaders());
    }

    public function getPath()
    {
        $domain = $_SERVER['SERVER_NAME'] ?: $_SERVER['HTTP_X_REWRITE_URL'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = preg_replace('~/+$~', '', $path);
        return $domain . '!' . $path;
    }

    public function existsGetData()
    {
        return 0 !== count($this->get);
    }

    public function existsPostData()
    {
        return 0 !== count($this->post);
    }

    public function existsFilesData()
    {
        return 0 !== count($this->files);
    }

    public function isUploaded($file)
    {
        return isset($this->files->{$file}) && ( is_array($this->files->{$file}) || \UPLOAD_ERR_OK == $this->files->{$file}->error );
    }

    public function isUploadedArray($file)
    {
        return $this->isUploaded($file) && is_array($this->files->{$file});
    }

    protected function getHttpHeaders()
    {
        if (function_exists('getallheaders'))
            return getallheaders();

        $ret = [];
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

}