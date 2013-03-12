<?php

namespace Dewdrop\Admin\Page;

use Dewdrop\Admin\ComponentAbstract;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\View\Model\ViewModel;

abstract class PageAbstract
{
    protected $component;

    protected $pagePath;

    protected $renderer;

    protected $view;

    public function __construct(ComponentAbstract $component, $pageFile)
    {
        $this->component = $component;
        $this->renderer  = new PhpRenderer();
        $this->view      = new ViewModel();

        $resolver = new Resolver\AggregateResolver();
        $this->renderer->setResolver($resolver);

        $stack = new Resolver\TemplatePathStack(
            array(
                'script_paths' => array(
                    dirname($pageFile) . '/view-scripts'
                )
            )
        );

        $resolver->attach($stack);

        $this->view->setTemplate('index');

        $this->init();
    }

    public function init()
    {
    }

    public function process()
    {

    }

    abstract public function render();

    protected function renderView()
    {
        echo $this->renderer->render($this->view);
    }
}
