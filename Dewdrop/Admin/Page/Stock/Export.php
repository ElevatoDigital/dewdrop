<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;

class Export extends PageAbstract
{
    /**
     * @var \Dewdrop\Admin\Component\CrudInterface
     */
    protected $component;

    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('export');
        $this->view->assign('component', $this->component);
    }
}
