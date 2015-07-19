<?php

namespace T4\Fs;

class Helpers
{

    public static function getRealPath($path)
    {
        if (0 === strpos($path, '///')) {
            return ROOT_PATH . DS . str_replace('/', DS, substr($path, 3));
        } elseif (0 === strpos($path, '//')) {
            return ROOT_PATH_PROTECTED . DS . str_replace('/', DS, substr($path, 2));
        } elseif (0 === strpos($path, '/')) {
            return ROOT_PATH_PUBLIC . DS . str_replace('/', DS, substr($path, 1));
        } else {
            return false;
        }
    }

    public static function listDir($path, $order = \SCANDIR_SORT_NONE)
    {
        if (!is_dir($path))
            throw new Exception('No such dir: ' . $path);
        return array_map(
            function ($f) use ($path) {
                return $path . DS . $f;
            },
            scandir($path, $order)
        );
    }

    public static function listDirRecursive($path)
    {
        $list = self::listDir($path);
        $ret = [];
        foreach ($list as $file) {
            if ('.' == basename($file) || '..' == basename($file)) {
                $ret[] = $file;
                continue;
            }
            if (is_dir($file)) {
                $ret = array_merge($ret, self::listDir($file));
            } else {
                $ret[] = $file;
            }
        }
        return $ret;
    }

    /**
     * Creates directory by absolute path
     * @param string $dirName Directory path
     * @param int $mode Access mode
     * @return bool
     * @throws \T4\Fs\Exception
     */
    public static function mkDir($dirName, $mode = 0777)
    {
        if (file_exists($dirName) && is_dir($dirName)) {
            return @chmod($dirName, $mode);
        }
        $result = @mkdir($dirName, $mode, true);
        if (false === $result) {
            throw new Exception('Can not create dir ' . $dirName);
        }
        return true;
    }


    /**
     * Копирует файл в заданный файл или папку
     * @param string $src Путь к файлу-источнику
     * @param string $dst Путь к файлу или папке назначения
     * @param int $mode Права доступа, которые будут установлены после копирования
     * @return int Длина файла
     * @throws \T4\Fs\Exception
     */
    public static function copyFile($src, $dst, $mode = 0644)
    {
        if ($src == $dst) {
            throw new Exception('Source and destination are the same file');
        }
        if (!is_readable($src)) {
            throw new Exception('File ' . $src . ' is not readable');
        }

        $dstPath = pathinfo($dst, PATHINFO_DIRNAME);
        if (!is_readable($dstPath))
            self::mkDir($dstPath);

        if (is_dir($dst)) {
            $dst = $dst . DS . basename($src);
        }

        $res = copy($src, $dst);
        if (!$res)
            throw new Exception('Can not copy file ' . $src . ' to ' . $dst);
        @chmod($dst, $mode);
        return true;

    }

    /**
     * Копирует рекурсивно содержимое папки-источника в заданную
     * @param $src Папка-источник
     * @param $dst Папка-приемник
     * @throws \T4\Fs\Exception
     */
    public static function copyDir($src, $dst)
    {
        $src = realpath($src);
        if (!is_readable($src)) {
            throw new Exception('Dir ' . $src . ' is not readable');
        }
        if (!is_dir($src)) {
            throw new Exception('' . $src . ' is not a directory');
        }

        if (!file_exists($dst)) {
            self::mkDir($dst);
        }
        $dst = realpath($dst);
        if (!is_readable($dst)) {
            throw new Exception('Dir ' . $dst . ' is not readable');
        }
        if (!is_dir($dst)) {
            throw new Exception('' . $dst . ' is not a directory');
        }

        foreach (self::listDir($src) as $file) {
            $fileName = basename($file);
            if ('.' == $fileName || '..' == $fileName) {
                continue;
            }
            if (!is_readable($file)) {
                throw new Exception($file . ' is not readable');
            }
            if (is_dir($file)) {
                self::copyDir($file, $dst . DS . $fileName);
            } else {
                self::copyFile($file, $dst);
            }
        }
    }

    /**
     * Удаляет файл по заданному пути
     * @param string $file
     * @return bool
     * @throws \T4\Fs\Exception
     */
    public static function removeFile($file)
    {
        if (!is_readable($file) || !is_file($file)) {
            throw new Exception('No such file: ' . $file);
        }
        $res = unlink($file);
        if (false === $res) {
            throw new Exception('File deleting failure: ' . $file);
        }
        return $res;
    }

    /**
     * Max filemtime at path recursive
     * @param string $path
     * @return int
     */
    static public function dirMTime($path)
    {
        clearstatcache();
        return max(array_map(function ($f) {
            return filemtime($f);
        }, self::listDirRecursive($path)));
    }

}