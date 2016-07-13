<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Env;

use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Component\ShellIntegrationInterface;
use Dewdrop\Pimple;
use Dewdrop\View\View;
use Silex\Application;
use Zend\View\Helper\HeadLink;
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
     * The path to the layout view script.
     *
     * @var string
     */
    private $layoutPath;

    /**
     * The file name of the view script used when rendering the layout.
     *
     * @var string
     */
    private $layoutName = 'silex.phtml';

    /**
     * Provide a \Silex\Application object that can be used to retrieve
     * resources, register routes, etc.
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->layoutPath  = __DIR__ . '/view-scripts';
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

    /**
     * Get the title for this admin environment.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Inflect a component name for use in URLs and routes.
     *
     * @param string $name
     * @return string
     */
    public function inflectComponentName($name)
    {
        return $this->application['inflector']->hyphenize($name);
    }

    /**
     * Initialize the provided component by setting up routes for it in Silex.
     *
     * @param ComponentInterface $component
     */
    public function initComponent(ComponentInterface $component)
    {
        $this->application->match(
            '/admin/' . $component->getName() . '/{page}',
            function ($page) use ($component) {
                /* @var $component ComponentInterface */
                foreach ($this->components as $preDispatchComponent) {
                    $preDispatchComponent->preDispatch();
                }

                return $component->dispatchPage($page);
            }
        );

        $this->application->get(
            '/admin/' . $component->getName(),
            function () use ($component) {
                $url = '/admin/' . $component->getName() . '/index';

                if (Pimple::hasResource('url-filter')) {
                    /* @var $filter callable */
                    $filter = Pimple::getResource('url-filter');
                    $url    = $filter($url);
                }

                return $this->application->redirect($url);
            }
        );
    }

    /**
     * Set the path where the layout view script can be found.
     *
     * @param $layoutPath
     * @return $this
     */
    public function setLayoutPath($layoutPath)
    {
        $this->layoutPath = $layoutPath;

        return $this;
    }

    /**
     * Set the file name of the layout view script.
     *
     * @param $layoutName string
     * @return $this
     */
    public function setLayoutName($layoutName)
    {
        $this->layoutName = $layoutName;

        return $this;
    }

    /**
     * Render the admin shell's layout, placing the supplied content in the
     * appropriate area of the HTML.
     *
     * @param string $content
     * @param HeadScript $headScript
     * @param HeadLink $headLink
     * @return string
     */
    public function renderLayout($content, HeadScript $headScript = null, HeadLink $headLink = null)
    {
        $view = Pimple::getResource('view');
        $view->setScriptPath($this->layoutPath);

        $session = ($this->session ?: $this->application['session']);

        if ($session->get('successMessage')) {
            $view->assign('successMessage', $session->get('successMessage'));
            $session->remove('successMessage');
        }

        $view
            ->assign('title', $this->title)
            ->assign('env', $this)
            ->assign('components', $this->getSortedComponentsForMenu())
            ->assign('content', $content)
            ->assign('user', (isset($this->application['user']) ? $this->application['user'] : null))
            ->assign('viewHeadScript', $headScript)
            ->assign('viewHeadLink', $headLink)
            ->assign('dependencies', $this->coreClientSideDependencies);

        return $view->render($this->layoutName);
    }

    /**
     * Generate a URL for the provided page and params that will match the
     * Silex routes set up by this class.
     *
     * @param ComponentInterface $component
     * @param string $page
     * @param array $params
     * @return string
     */
    public function url(ComponentInterface $component, $page, array $params = array())
    {
        $url = '/admin/'
            . $component->getName() . '/'
            . $this->application['inflector']->hyphenize($page)
            . $this->assembleQueryString($params, '?');

        if (Pimple::hasResource('url-filter')) {
            /* @var $filter callable */
            $filter = Pimple::getResource('url-filter');
            $url    = $filter($url);
        }

        return $url;
    }

    /**
     * Redirect to the provided URL using Silex.
     *
     * @param string $url
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($url)
    {
        return $this->application->redirect($url);
    }

    /**
     * Build an array of components sorted by the menu position property defined
     * in each component, falling back to the component titles if no position is
     * set.
     *
     * @return array
     */
    protected function getSortedComponentsForMenu()
    {
        $components = $this->components;

        usort(
            $components,
            function ($a, $b) {
                /* @var $a ShellIntegrationInterface */
                /* @var $b ShellIntegrationInterface */
                $aPos = $a->getMenuPosition();
                $bPos = $b->getMenuPosition();

                // Sort by title, if no menu positions are set
                if (null === $aPos && null === $bPos) {
                    return strcasecmp($a->getTitle(), $b->getTitle());
                }

                // Sort components with no position assigned to the end of the list
                $aPos = (null === $aPos ? 100 : $aPos);
                $bPos = (null === $bPos ? 100 : $bPos);

                if ($aPos === $bPos) {
                    return 0;
                }

                return ($aPos < $bPos) ? -1 : 1;
            }
        );

        return $components;
    }
}
