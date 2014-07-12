<?php

namespace Dewdrop\Auth\Page;

use Dewdrop\Auth;
use Dewdrop\View\View;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

abstract class PageAbstract
{
    protected $auth;

    protected $app;

    protected $request;

    protected $view;

    public function __construct(Auth $auth, Application $app, Request $request, View $view = null)
    {
        $this->auth    = $auth;
        $this->app     = $app;
        $this->request = $request;
        $this->view    = ($view ?: new View());

        $this->view->setScriptPath(__DIR__ . '/view-scripts');
        $this->view->assign('title', $this->auth->getTitle());

        $this->init();
    }

    public function init()
    {

    }

    public function renderLayout($content)
    {
        $layout = new View();

        $layout->setScriptPath($this->auth->getLayoutScriptPath());

        $layout->assign(
            array(
                'content' => $content,
                'title'   => $this->auth->getTitle(),
                'view'    => $this->view
            )
        );

        return $layout->render($this->auth->getLayoutScript());
    }

    abstract public function respond();
}
