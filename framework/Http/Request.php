<?php

namespace T4\Http;

use T4\Core\Std;


/**
 * Class Request
 * @package T4\Http
 * @var \T4\Core\Std $get
 */
class Request
    extends Std
{

    public function __construct()
    {
        $this->get = new Std();
        $this->get->merge($_GET);
        unset($this->get->__path);

        $this->post = new Std();
        $this->post->merge($_POST);

        $this->files = new Std();
        if (!empty($_FILES)) {
            foreach ($_FILES as $name => $data) {
                if (is_array($data['name'])) {
                    $this->files->$name = [];
                    foreach ($data['name'] as $n => $d) {
                        $this->files->$name[$n] = new Std();
                        $this->files->$name[$n]->merge($d);
                    }
                } else {
                    $this->files->$name->merge($data);
                }
            }
        }
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

} 