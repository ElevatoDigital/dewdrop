<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields;

class DebugFields extends PageAbstract
{
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $this->view->displayFields   = new Fields();
        $this->view->componentFields = $this->component->getFields();
    }
}
