<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields;

class DebugFields extends PageAbstract
{
    public function render()
    {
        $this->view->displayFields   = new Fields();
        $this->view->componentFields = $this->component->getFields();
    }
}
