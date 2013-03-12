<?php

namespace Dewdrop;

class Inflector
{
    protected $baseComponentPath;

    protected $baseModelPath;

    public function __construct()
    {
        $this->baseComponentPath = dirname(dirname(__DIR__)) . '/admin';
        $this->baseModelPath     = dirname(dirname(__DIR__)). '/models';
    }

    public function getComponentClassPath($path)
    {
        $folder = basename($path);
        $full   = $this->baseComponentPath . '/' . $folder;

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
        return $this->baseModelPath . '/' . $name . '.php';
    }

    public function getModelClass($name)
    {
        return '\Model\\' . $name;
    }
}
