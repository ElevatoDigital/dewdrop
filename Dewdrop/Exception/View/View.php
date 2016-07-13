<?php

namespace Dewdrop\Exception\View;

use Dewdrop\View\View as BaseView;

class View extends BaseView
{
    public function init()
    {
        $this
            ->registerHelper('trace', '\Dewdrop\Exception\View\Helper\Trace');
    }
}
