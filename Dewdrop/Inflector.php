<?php

namespace Dewdrop;

use Dewdrop\Paths;

class Inflector
{
    private $paths;

    public function __construct()
    {
        $this->paths = new Paths();
    }

    public function getComponentClassPath($path)
    {
        $folder = basename($path);
        $full   = $this->paths->getAdmin() . '/' . $folder;

        return $full . '/Component.php';
    }

    public function getComponentClass($path)
    {
        $words = explode('-', $path);
        $words = array_map('ucfirst', $words);

        return '\Admin\\' . implode('', $words) . '\\Component';
    }

    public function getModelClassPath($name)
    {
        return $this->paths->getModels() . '/' . $name . '.php';
    }

    public function getModelClass($name)
    {
        return '\Model\\' . $name;
    }
}
