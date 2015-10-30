<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\Env\EnvInterface;
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
 * into the admin shell.  For example, the $title property determines what
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
     * The title to display for this component in the admin navigation.
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
     * The position this item should take in the admin navigation menu.
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

    /**
     * Content that should be displayed in a "badge" alongside this component's
     * menu item.
     *
     * @var string
     */
    private $badgeContent = null;

    /**
     * The page factories registered with this component to provide page objects.
     *
     * @var array
     */
    private $pageFactories = array();

    /**
     * A page factory to allow overriding of page classes that come from other
     * factories.
     *
     * @var \Dewdrop\Admin\PageFactory\Custom
     */
    private $customPageFactory;

    /**
     * The permissions for this component.
     *
     * @var Permissions
     */
    private $permissions;

    /**
     * Whether the admin environment should wrap the page environment with the
     * layout (i.e. admin shell chrome.
     *
     * @var bool
     */
    protected $shouldRenderLayout = true;

    /**
     * The Pimple object used to supply dependencies to the admin component.
     * Basically used as a service locator in this context, which makes testability
     * a bit trickier in the context of the component class itself.  However, it
     * makes using component classes in other contexts (e.g. testing your page
     * classes) much easier.
     *
     * @var Pimple
     */
    protected $pimple;

    /**
     * The admin environment.  Used to do anything environment (i.e. WP or Silex)
     * specific, like perform redirects.
     *
     * @var EnvInterface
     */
    protected $env;

    /**
     * The path to the component class.
     *
     * @var string
     */
    protected $path;

    /**
     * The component name (as it would show up in the URL, for example).
     *
     * @var string
     */
    protected $name;

    /**
     * Whether this component is active (currently in charge of dispatching a
     * page/response).
     *
     * @var boolean
     */
    private $active = false;

    /**
     * Callbacks registered with this component via onPageDispatch().
     *
     * @var array
     */
    private $pageDispatchCallbacks = [];

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
        $this->env = $this->getPimpleResource('admin');

        // Component metadata retrieved via reflection
        $reflectionClass = new ReflectionClass($this);

        $this->path = dirname($reflectionClass->getFileName());
        $this->name = basename($this->path);

        // Setup the default page factory, which looks for files in the component's folder
        $this->addPageFactory(new PageFilesFactory($this));

        if (isset($pimple['custom-page-factory'])) {
            $this->customPageFactory = $this->pimple['custom-page-factory'];
            $this->customPageFactory->setComponent($this);
            $this->addPageFactory($this->customPageFactory);
        }

        $this->init();

        $this->checkRequiredProperties();
    }

    /**
     * Your component sub-class will use the init method to set basic component
     * information like the title and the icon.
     */
    abstract public function init();

    /**
     * This method will be called before any admin page is dispatched.
     */
    public function preDispatch()
    {

    }

    /**
     * Check to see if the supplied resource is present in the component's Pimple
     * or in the Dewdrop Pimple.
     *
     * @param string $name
     * @return bool
     */
    public function hasPimpleResource($name)
    {
        return isset($this->pimple[$name]) || DewdropPimple::hasResource($name);
    }

    /**
     * Get the named Pimple resource from the component's local container or the
     * Dewdrop container.
     *
     * @param $name
     * @return mixed
     */
    public function getPimpleResource($name)
    {
        if (isset($this->pimple[$name])) {
            return $this->pimple[$name];
        } else {
            return DewdropPimple::getResource($name);
        }
    }

    /**
     * Get all the page factories associated with this component.
     *
     * @return array
     */
    public function getPageFactories()
    {
        return $this->pageFactories;
    }

    /**
     * Get the custom page factory for this component.
     *
     * @return \Dewdrop\Admin\PageFactory\Custom
     */
    public function getCustomPageFactory()
    {
        return $this->customPageFactory;
    }

    /**
     * Add a new page factory to this component.
     *
     * @param PageFactoryInterface $pageFactory
     * @return $this
     */
    public function addPageFactory(PageFactoryInterface $pageFactory)
    {
        $this->pageFactories[] = $pageFactory;

        return $this;
    }

    /**
     * Get the Pimple container used by this component.
     *
     * @return Pimple
     */
    public function getPimple()
    {
        return $this->pimple;
    }

    /**
     * Get the path to this component's class.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get a Inflector object.
     *
     * @deprecated
     * @return \Dewdrop\Inflector
     */
    public function getInflector()
    {
        return $this->getPimpleResource('inflector');
    }

    /**
     * Get a Paths object.
     *
     * @deprecated
     * @return \Dewdrop\Paths
     */
    public function getPaths()
    {
        return $this->getPimpleResource('paths');
    }

    /**
     * Get a Request object.
     *
     * @return \Dewdrop\Request
     */
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

    /**
     * Check to see if this component is active.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Get the Permissions object for this component.
     *
     * @return Permissions
     */
    public function getPermissions()
    {
        if (!$this->permissions) {
            $this->permissions = new Permissions($this);
        }

        return $this->permissions;
    }

    /**
     * Iterate over the page factories, attempting to create a page object for
     * the supplied name.  If no page factory can handle the page name, that's
     * a 404.
     *
     * @param string $name
     * @return \Dewdrop\Admin\Page\PageAbstract|false
     * @throws Exception
     */
    public function createPageObject($name)
    {
        $page = false;

        /* @var $factory PageFactoryInterface */
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
            'title' => $title,
            'route' => ucfirst($page),
            'params' => $params,
            'url' => $this->url($page, $params)
        );

        return $this;
    }

    /**
     * Add a divider to the component's submenu.
     *
     * @return $this
     */
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

    /**
     * Get the name of the component (i.e. the string that would represent it
     * in the URL.
     *
     * @return string
     */
    public function getName()
    {
        return $this->env->inflectComponentName($this->name);
    }

    /**
     * Get a fully-qualified name for the component that can be used when
     * referencing the component in externals systems like the DB.
     *
     * @return string
     */
    public function getFullyQualifiedName()
    {
        return '/application/admin/' . $this->getName();
    }

    /**
     * Get an identifier that can be used when storing this component's
     * listing query string parameters in the session.  We store the params
     * so that we can redirect while maintaining filter and pagination
     * state.
     *
     * @return string
     */
    public function getListingQueryParamsSessionName()
    {
        return rtrim($this->getFullyQualifiedName(), '/') . '/listing-query-params';
    }

    /**
     * Set whether the admin environment should wrap the page's output with
     * the layout (the admin shell chrome).
     *
     * @param boolean $shouldRenderLayout
     * @return $this
     */
    public function setShouldRenderLayout($shouldRenderLayout)
    {
        $this->shouldRenderLayout = $shouldRenderLayout;

        return $this;
    }

    /**
     * Check to see whether the page output should be wrapped by the layout.
     *
     * @return bool
     */
    public function shouldRenderLayout()
    {
        return $this->shouldRenderLayout;
    }

    /**
     * Set the title of this component as it should be displayed in the
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

    /**
     * Get the title of the component that would be displayed in the admin menu.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the badge content that should be displayed alongside this component's
     * navigation.
     *
     * @param string $badgeContent
     * @return $this
     */
    public function setBadgeContent($badgeContent)
    {
        $this->badgeContent = $badgeContent;

        return $this;
    }

    /**
     * Get the badge content assigned to this component.
     *
     * @return string
     */
    public function getBadgeContent()
    {
        return $this->badgeContent;
    }

    /**
     * Set the icon that should be used in this component's admin menu
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

    /**
     * Get the icon that should be displayed for the component in the
     * admin menu.
     *
     * @return string
     */
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

    /**
     * Get the position at which this component should be rendered in the
     * admin shell nav menu.
     *
     * @return int
     */
    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

    /**
     * Register a callback to run when the page matching the supplied name
     * is dispatched on this component.  Your callback will receive the new
     * page object as its first argument.
     *
     * @param $pageName
     * @param callable $callback
     * @return $this
     */
    public function onPageDispatch($pageName, callable $callback)
    {
        if (!array_key_exists($pageName, $this->pageDispatchCallbacks)) {
            $this->pageDispatchCallbacks[$pageName] = [];
        }

        $this->pageDispatchCallbacks[$pageName][] = $callback;

        return $this;
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
     * @return mixed
     */
    public function dispatchPage($page = null, Response $response = null)
    {
        $this->active = true;

        if (!$this->getPermissions()->can('access')) {
            return $this->env->redirect('/admin/');
        }

        if (is_string($page)) {
            $name = $page;
            $page = $this->createPageObject($page);

            if (array_key_exists($name, $this->pageDispatchCallbacks)) {
                foreach ($this->pageDispatchCallbacks[$name] as $callback) {
                    call_user_func($callback, $page);
                }
            }
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

        if (is_array($output)) {
            $this->renderJsonResponse($output);
        } elseif (!$output) {
            // Capture output generated during render rather than returned
            $output = ob_get_clean();
        }

        // Automatically render view if no output is generated
        if (!$output) {
            $output = $page->renderView();
        }

        if (!$this->shouldRenderLayout) {
            return $output;
        } else {
            return $this->env
                ->setActiveComponent($this)
                ->renderLayout(
                    $output,
                    $page->getView()->headScript(),
                    $page->getView()->headLink()
                );
        }
    }

    /**
     * Render the supplied output as a JSON response.  This method is mostly
     * in place to allow mocking (and thus dodging the exit statement) during
     * testing.
     *
     * @param array $output
     * @return void
     */
    protected function renderJsonResponse(array $output)
    {
        header('Content-Type: application/json');
        echo json_encode($output);
        exit;
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
}
