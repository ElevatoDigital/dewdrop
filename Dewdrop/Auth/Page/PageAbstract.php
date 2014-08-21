<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Auth\Page;

use Dewdrop\Auth;
use Dewdrop\View\View;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Abstract page class
 */
abstract class PageAbstract
{
    /**
     * Silex application
     *
     * @var Application
     */
    protected $app;

    /**
     * Auth component
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Request object
     *
     * @var Request
     */
    protected $request;

    /**
     * View object
     *
     * @var View
     */
    protected $view;

    /**
     * Constructor
     *
     * @param Auth $auth
     * @param Application $app
     * @param Request $request
     * @param View $view
     */
    public function __construct(Auth $auth, Application $app, Request $request, View $view = null)
    {
        $this->auth    = $auth;
        $this->app     = $app;
        $this->request = $request;
        $this->view    = ($view ?: new View());

        $this->view
            ->setScriptPath(__DIR__ . '/view-scripts')
            ->assign('title', $this->auth->getTitle())
            ->assign('headerHtml', $this->auth->getHeaderHtml());

        $this->init();
    }

    /**
     * Initializations
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Renders the given content within the layout
     *
     * @param string $content
     * @return string
     */
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

    /**
     * Provides a string response to the request
     *
     * @return string
     */
    abstract public function respond();
}
