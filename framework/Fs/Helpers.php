<?php

namespace T4\Fs;

class Helpers
{

    /**
     * Создает папку по указанному пути
     * @param $dirName Имя папки
     * @param int $mode Права доступа к создаваемой папке
     * @throws \T4\Fs\Exception
     */
    public static function mkDir($dirName, $mode = 0777)
    {
        $result = mkdir($dirName, $mode, true);
        if (!$result)
            throw new Exception('Can not create dir '.$dirName);
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