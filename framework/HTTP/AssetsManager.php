<?php

namespace T4\HTTP;


class AssetsManager
{

    public function __invoke($path) {
        $dir = pathinfo(realpath(ROOT_PATH.$path), PATHINFO_DIRNAME);
        $file = pathinfo(realpath(ROOT_PATH.$path), PATHINFO_BASENAME);
        $hash = substr(md5($dir),0,16);
        $symlink = ROOT_PATH.DS.'assets'.DS.$hash;
        if ( !file_exists($symlink) ) {
            symlink($dir, $symlink);
        }
        return $symlink.DS.$file;
    }

}