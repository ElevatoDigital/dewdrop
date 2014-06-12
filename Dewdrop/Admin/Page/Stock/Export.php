<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;

class Export extends PageAbstract
{
    /**
     * @var \Dewdrop\Admin\Component\Silex\CrudAbstract
     */
    protected $component;

    /**
     * @inheritdoc
     */
    public function render()
    {
        // Inject component into view
        $this->view->assign('component', $this->component);
    }
}