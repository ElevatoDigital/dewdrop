<?php

namespace DewdropTest\Admin\Animals;

use Dewdrop\Admin\ComponentAbstract;

class Component extends ComponentAbstract
{
    public function init()
    {
        $this->setTitle('Animals');
    }
}
