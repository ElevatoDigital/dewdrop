<?php

namespace Dewdrop\Admin\Page;

use Dewdrop\Admin\ComponentAbstract;
use Dewdrop\Request;
use Dewdrop\View\View;

abstract class PageAbstract
{
    protected $component;

    protected $view;

    protected $request;

    public function __construct(ComponentAbstract $component, $pageFile)
    {
        $this->component = $component;
        $this->view      = new View();
        $this->request   = new Request();

        $this->view->setScriptPath(dirname($pageFile) . '/view-scripts');
    }

    public function init()
    {
    }

    public function shouldProcess()
    {
        return true;
    }

    public function process()
    {

    }

    abstract public function render();

    public function renderView()
    {
        echo $this->view->render($this->inflectViewScriptName());
    }

    private function inflectViewScriptName()
    {
        $className = get_class($this);
        $pageName  = substr($className, strrpos($className, '\\') + 1);
        $words     = implode('-', preg_split('/(?=[A-Z])/', $pageName));

        return strtolower($pageName . '.phtml');
    }
}
