<?php


namespace T4\Fs;


use T4\Core\Exception;

class Helpers
{


    /**
     * Копирует файл в заданный файл или папку
     * @param $src Путь к файлу-источнику
     * @param $dst Путь к файлу или папке назначения
     * @param int $mode Права доступа, которые будут установлены после копирования
     * @return int Длина файла
     * @throws Exception
     */
    public static function copyFile($src, $dst, $mode = 0644)
    {
        if (!is_readable($src)) {
            throw new Exception('File ' . $src . ' is not readable');
        }

        if (is_dir($dst)) {
            if (!is_readable($dst)) {
                throw new Exception('Dir ' . $dst . ' is not readable');
            }
            $dst = $dst . DS . basename($src);
        }

        $srcLength = filesize($src);
        $srcFile = fopen($src, 'r');
        $dstFile = fopen($dst, 'w+');
        $copyLength = stream_copy_to_stream($srcFile, $dstFile);
        fclose($srcFile);
        fclose($dstFile);

        if ($copyLength != $srcLength) {
            unlink($dst);
            throw new Exception('Can not copy file ' . $src . ' to ' . $dst);
        }

        chmod($dst, $mode);
        return $copyLength;

    }

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
            mkdir($dst, 0777, true);
        }
        $dst = realpath($dst);
        if (!is_readable($dst)) {
            throw new Exception('Dir ' . $dst . ' is not readable');
        }
        if (!is_dir($dst)) {
            throw new Exception('' . $dst . ' is not a directory');
        }

        foreach (scandir($src) as $filename) {
            if ( '.'==$filename || '..'==$filename ) {
                continue;
            }
            $fullFileName = $src.DS.$filename;
            if ( !is_readable($fullFileName) ) {
                throw new Exception($fullFileName . ' is not readable');
            }
            if (is_dir($fullFileName)) {
                self::copyDir($fullFileName, $dst.DS.$filename);
            } else {
                self::copyFile($fullFileName, $dst);
            }
        }
    }

}