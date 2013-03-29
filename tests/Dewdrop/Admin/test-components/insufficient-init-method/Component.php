<?php

namespace DewdropTest\Admin\InsufficientInitMethod;

use Dewdrop\Admin\ComponentAbstract;

class Component extends ComponentAbstract
{
    public function init()
    {
        // I should be setting required parameters here, but I'm not.
    }
}
