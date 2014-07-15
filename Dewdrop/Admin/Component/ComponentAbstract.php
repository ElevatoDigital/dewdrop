<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\PageFactory\Files as PageFilesFactory;
use Dewdrop\Admin\PageFactory\PageFactoryInterface;
use Dewdrop\Admin\Permissions;
use Dewdrop\Admin\Response;
use Dewdrop\Exception;
use Dewdrop\Pimple as DewdropPimple;
use Pimple;
use ReflectionClass;

/**
 * This class enables you to define how your component should appear and wire
 * into the WP admin shell.  For example, the $title property determines what
 * your plugin will be labeled in the admin navigation and the addToSubmenu()
 * method will allow you to add submenu items for your component.
 */
abstract class ComponentAbstract
{
    /**
     * An array of submenu pages that have been added by calling addToSubmenu().
     * These are actually tied in by registerMenuPage() after the admin_menu
     * hook is triggered.
     *
     * @var array
     */
    protected $submenuPages = array();

    /**
     * The title to display for this component in the WP admin navigation.
     *
     * @var string
     */
    private $title;

    /**
     * The icon that should be used for this component's navigation link.
     *
     * @var string
     */
    private $icon;

    /**
     * The position this item should take in the WP admin navigation menu.
     *
     * To see the current menu positions, you can var_dump($_GLOBALS['menu']).
     * Note that is you pick an already used menu position, you will essentially
     * hide that item.  For example, if you set your menu position to "5",
     * which is the default value for "Posts", your component will show up there
     * instead of "Posts", rather than just pushing "Posts" further down like
     * you might expect.  You can leave $menuPosition blank if you'd simply
     * like your component to be appended to the end of the menu.
     *
     * @var integer
     */
    private $menuPosition = null;

    private $pageFactories = array();

    private $permissions;

    protected $shouldRenderLayout = true;

    /**
     * Create a component instance using the DB adapter creating by the Wiring
     * class.
     *
     * @param Pimple $pimple
     * @param string $componentName
     */
    public function __construct(Pimple $pimple = null)
    {
        $this->pimple = ($pimple ?: DewdropPimple::getInstance());
        $this->env    = $this->getPimpleResource('admin');

        // Component metadaata retrieved via reflection
        $reflectionClass = new ReflectionClass($this);

        $this->path = dirname($reflectionClass->getFileName());
        $this->name = basename($this->path);

        // Setup the default page factory, which looks for files in the component's folder
        $this->addPageFactory(new PageFilesFactory($this));

        $this->init();

        $this->checkRequiredProperties();
    }

    /**
     * Your component sub-class will use the init method to set basic component
     * information like the title and the icon.
     */
    abstract public function init();

    public function getPageFactories()
    {
        return $this->pageFactories;
    }

    public function addPageFactory(PageFactoryInterface $pageFactory)
    {
        $this->pageFactories[] = $pageFactory;

        return $this;
    }

    public function getPimple()
    {
        return $this->pimple;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getInflector()
    {
        return $this->getPimpleResource('inflector');
    }

    public function getPaths()
    {
        return $this->getPimpleResource('paths');
    }

    public function getRequest()
    {
        return $this->getPimpleResource('dewdrop-request');
    }

    /**
     * Get the DB adapter associated with this component.  This is used
     * frequently by page classes to get access to the DB connection.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getDb()
    {
        return $this->getPimpleResource('db');
    }

    public function getPermissions()
    {
        if (!$this->permissions) {
            $this->permissions = new Permissions($this);
        }

        return $this->permissions;
    }

    public function createPageObject($name)
    {
        $page = null;

        foreach ($this->getPageFactories() as $factory) {
            $page = $factory->createPage($name);

            if ($page) {
                break;
            }
        }

        if (!$page) {
            throw new Exception('Could not find page');
        }

        return $page;
    }

    /**
     * Request that the admin Env assemble a URL that will work to get to the
     * specified page.
     *
     * @param string $page
     * @param array $params
     * @returns string
     */
    public function url($page, array $params = array())
    {
        return $this->env->url($this, $page, $params);
    }

    /**
     * Add a link to the submenu for this component.
     *
     * @param string $title
     * @param string $page
     * @param array $params
     * @return ComponentAbstract
     */
    public function addToSubmenu($title, $page, $params = array())
    {
        $this->submenuPages[] = array(
            'title'  => $title,
            'route'  => ucfirst($page),
            'params' => $params,
            'url'    => $this->url($page, $params)
        );

        return $this;
    }

    public function addSubmenuDivider()
    {
        $this->submenuPages[] = array(
            'isDivider' => true
        );

        return $this;
    }

    /**
     * Get all sub-menu pages that have been added to this component.
     *
     * @return array
     */
    public function getSubmenuPages()
    {
        return $this->submenuPages;
    }

    public function getName()
    {
        return $this->env->inflectComponentName($this->name);
    }

    public function getFullyQualifiedName()
    {
        return '/application/admin/' . $this->getName();
    }

    public function setShouldRenderLayout($shouldRenderLayout)
    {
        $this->shouldRenderLayout = $shouldRenderLayout;

        return $this;
    }

    public function shouldRenderLayout()
    {
        return $this->shouldRenderLayout;
    }

    /**
     * Set the title of this component as it should be displayed in the WP
     * admin menu.
     *
     * @param string $title
     * @return ComponentAbstract
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the icon that should be used in this component's WP admin menu
     * entry.
     *
     * @param string $icon
     * @return ComponentAbstract
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set the position of the component's menu entry
     *
     * @see $menuPosition
     * @param integer $menuPosition
     * @return ComponentAbstract
     */
    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

    /**
     * Make sure the title property was set in the component's init()
     * method.
     *
     * @throws Exception
     */
    protected function checkRequiredProperties()
    {
        if (!$this->title) {
            throw new Exception('Component title is required');
        }
    }

    /**
     * Dispatch the routed page, working through its init(), process() and
     * render() methods.
     *
     * Each abstract page class can specify whether the process() method
     * should actually be run by implementing a shouldProcess() method.
     * The page's init() method is guaranteed to always be called.  If
     * after calling render there is no output, the component will attempt
     * to render the page's default view script automatically.
     *
     * @param mixed $page
     * @param Response $response
     * @return void
     */
    public function dispatchPage($page = null, Response $response = null)
    {
        if (!$this->getPermissions()->can('access')) {
            return $this->env->redirect('/admin/');
        }

        if (is_string($page)) {
            $page = $this->createPageObject($page);
        }

        if (null === $response) {
            $response = new Response($page, array($this->env, 'redirect'));
        }

        $page->init();

        if ($page->shouldProcess()) {
            $responseHelper = $page->createResponseHelper(array($this->env, 'redirect'));

            $page->process($responseHelper);

            $response
                ->setWasProcessed(true)
                ->setHelper($responseHelper);

            $result = $response->executeQueuedActions();

            if ($result) {
                return $result;
            }
        }

        ob_start();

        $output = $page->render();

        // Capture output generated during render rather than returned
        if (!$output) {
            $output = ob_get_clean();
        }

        // Automatically render view if no output is generated
        if (!$output) {
            $output = $page->renderView();
        }

        if (!$this->shouldRenderLayout) {
            return $output;
        } else {
            return $this->env->renderLayout(
                $output,
                $page->getView()->headScript(),
                $page->getView()->headLink()
            );
        }
    }

    /**
     * Get WP slug for this component.
     *
     * We use the component name, with namespace back slashes replaced with
     * URL-friendly front slashes, as the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        $fullClass = str_replace('\\', '/', get_class($this));
        $segments  = explode('/', $fullClass);
        $nameIndex = count($segments) - 2;

        return $segments[$nameIndex];
    }

    private function getPimpleResource($name)
    {
        return (isset($this->pimple[$name]) ? $this->pimple[$name] : DewdropPimple::getResource($name));
    }
}
