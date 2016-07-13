<?php

namespace Dewdrop\Zf1\DewdropOptions;

class ValuePath
{
    private $path;

    private $key;

    private $value;

    public function __construct($path, $key, $value)
    {
        $this->path  = $path;
        $this->key   = $key;
        $this->value = $value;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }
}
