<?php

namespace DewdropTest\Admin\Animals;

use Dewdrop\Admin\Component\ComponentAbstract;

class Component extends ComponentAbstract
{
    public function init()
    {
        $this->setTitle('Animals');
    }
}
