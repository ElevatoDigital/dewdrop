<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Env;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\View\View;
use Silex\Application;
use Zend\View\Helper\HeadScript;

/**
 * This class is responsible for the shell-level admin functionality in Silex
 * applications.  It will wrap Component responses in an admin shell layout when
 * appropriate, assist in the finding and registering of admin components, etc.
 */
class Silex extends EnvAbstract
{
    /**
     * The title for the admin area.  Typically your application's name, the
     * name of the client's company, etc.
     *
     * @var string
     */
    private $title = 'Admin';

    /**
     * The \Silex\Application object.
     *
     * @var Application
     */
    private $application;

    /**
     * Provide a \Silex\Application object that can be used to retrieve
     * resources, register routes, etc.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Get the \Silex\Application used by the admin shell.
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the title of this admin area.  Will be displayed in the main
     * nav, etc.
     *
     * @param string $title
     * @return Silex
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function initComponent(ComponentAbstract $component)
    {
        $this->application->match(
            '/admin/' . $component->getName() . '/{page}',
            function ($page) use ($component) {
                return $component->dispatchPage($page);
            }
        )
        ->value('page', 'index');
    }

    /**
     * Render the admin shell's layout, placing the supplied content in the
     * appropriate area of the HTML.
     *
     * @param string $content
     * @return string
     */
    public function renderLayout($content, HeadScript $headScript = null)
    {
        $view = new View();
        $view->setScriptPath(__DIR__ . '/view-scripts');

        $view
            ->assign('title', $this->title)
            ->assign('components', $this->components)
            ->assign('content', $content)
            ->assign('viewHeadScript', $headScript);

        return $view->render('silex.phtml');
    }

    public function url(ComponentAbstract $component, $page, array $params = array())
    {
        return '/admin/'
            . $component->getName() . '/'
            . $this->application['inflector']->hyphenize($page)
            . $this->assembleQueryString($params, '?');
    }

    public function redirect($url)
    {
        return $this->application->redirect($url);
    }
}
