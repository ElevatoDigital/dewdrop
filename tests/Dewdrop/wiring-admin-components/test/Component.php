<?php

namespace Admin\Test;

use Dewdrop\Admin\ComponentAbstract;

class Component extends ComponentAbstract
{
    public function init()
    {
        $this->setTitle('Test Component');
    }
}
