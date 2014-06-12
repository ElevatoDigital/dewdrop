<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;

class DebugPages
{
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $this->view->pageFactories = $this->component->getPageFactories();
    }
}
