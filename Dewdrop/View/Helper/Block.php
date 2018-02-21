<?php

namespace Dewdrop\View\Helper;

use Dewdrop\View\Block as BlockObject;

class Block extends AbstractHelper
{
    /**
     * @var BlockObject[]
     */
    private $blocks = [];

    /**
     * @var bool
     */
    private $debug = false;

    public function direct($name = null)
    {
        if ($name) {
            return $this->get($name);
        } else {
            return $this;
        }
    }

    public function get($name)
    {
        if (!array_key_exists($name, $this->blocks)) {
            $block = new BlockObject($this->view, $name);
            $block->setDebug($this->debug);
            $this->blocks[$name] = $block;
        }

        return $this->blocks[$name];
    }

    public function enableDebugging()
    {
        $this->debug = true;

        return $this;
    }

    public function disableDebugging()
    {
        $this->debug = false;

        return $this;
    }
}