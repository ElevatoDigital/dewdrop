<?php

namespace Dewdrop\Upload\Filter;

use Dewdrop\SetOptionsTrait;
use Imagick;
use Zend\Filter\FilterInterface;

class ScaleImage implements FilterInterface
{
    use SetOptionsTrait;

    private $height;

    private $width;

    private $path;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function filter($value)
    {
        $image = new Imagick($value);
        $image->setImageBackgroundColor('#ffffff');

        list($originalWidth, $originalHeight) = getimagesize($value);

        // If the original image is smaller than the thumbnail size, don't scale
        if ($originalWidth <= $this->width && $originalHeight < $this->height) {
            if (null !== $this->path) {
                copy($value, $this->path);
                return $this->path;
            } else {
                return $value;
            }
        }

        if ($originalWidth > $originalHeight) {
            $image->scaleImage($this->width, 0);
        } else if ($originalHeight > $originalWidth) {
            $image->scaleImage(0, $this->height);
        } else {
            $image->scaleImage($this->width, $this->height);
        }

        if (null === $this->path) {
            $path = $value;
        } else {
            $path = $this->path;
        }

        $image->writeImage($path);

        return $value;
    }
}
