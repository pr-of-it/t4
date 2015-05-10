<?php

namespace T4\Fs;

class ImageProcessor
{

    public function __construct($path)
    {
        $this->path = \T4\Fs\Helpers::getRealPath($path);
        $imgdata = file_get_contents($this->path);
        $this->image = imagecreatefromstring($imgdata);
    }

    public function filterResize($width, $height)
    {
        $newimage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newimage, $this->image, 0, 0, 0, 0, $width, $height,
            $this->getWidth(), $this->getHeight());
        $this->image = $newimage;
        return $this;
    }

    public function filterResizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->filterResize($width, $height);
        return $this;
    }

    public function filterResizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getHeight() * $ratio;
        $this->filterResize($width, $height);
        return $this;
    }

    public function filterZoom($zoom)
    {
        $width = $this->getWidth() * $zoom / 100;
        $height = $this->getHeight() * $zoom / 100;
        $this->filterResize($width, $height);
        return $this;
    }

    public function save($imagetype = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($imagetype == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $this->path, $compression);
        } elseif ($imagetype == IMAGETYPE_GIF) {
            imagegif($this->image, $this->path);
        } elseif ($imagetype == IMAGETYPE_PNG) {
            imagepng($this->image, $this->path);
        }
        if ($permissions != null) {
            chmod($this->path, $permissions);
        }
        return $this;
    }

    public function getWidth()
    {
        return imagesx($this->image);
    }

    public function getHeight()
    {
        return imagesy($this->image);
    }

} 