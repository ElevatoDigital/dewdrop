<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Admin\Response;
use Dewdrop\Db\Adapter;
use Dewdrop\Admin\PageFactory\Files as PageFilesFactory;
use Dewdrop\Paths;
use Dewdrop\Request;
use Pimple;

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
    private $menuPosition;

    /**
     * A request object that makes it easier to work with GET and POST
     *
     * @var \Dewdrop\Request
     */
    protected $request;

    private $inflector;

    private $name;

    private $pimple;

    private $pageFactories = array();

    protected $redirector;

    /**
     * Create a component instance using the DB adapter creating by the Wiring
     * class.
     *
     * @param Adapter $db
     * @param Paths $paths
     * @param Request $request
     */
    public function __construct(Pimple $pimple, $componentName)
    {
        $this->pimple    = $pimple;
        $this->db        = $pimple['db'];
        $this->paths     = $pimple['paths'];
        $this->request   = $pimple['dewdrop-request'];
        $this->inflector = $pimple['inflector'];
        $this->name      = $componentName;

        $this->pageFactories[] = new PageFilesFactory($this, $this->request);

        $this->init();

        $this->redirector = function ($url) {
            wp_safe_redirect($url);
            exit;
        };

        $this->checkRequiredProperties();
    }

    public function getPageFactories()
    {
        return $this->pageFactories;
    }

    public function addPageFactory($pageFactory)
    {
        $this->pageFactories[] = $pageFactory;

        return $this;
    }

    public function getPath()
    {
        return $this->paths->getAdmin() . '/' . $this->name;
    }

    public function getInflector()
    {
        return $this->inflector;
    }

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Your component sub-class will use the init method to set basic component
     * information like the title and the icon.
     */
    abstract public function init();

    /**
     * Add an "admin_menu" callback to let WP know that you want to register
     * an admin component.
     */
    public function register()
    {
        add_action('admin_init', array($this, 'adminInit'));
        add_action('admin_menu', array($this, 'registerMenuPage'));

        // Also allow routing via WP's ajax facility to avoid rendering layout
        add_action('wp_ajax_' . $this->getSlug(), array($this, 'route'));
    }

    /**
     * Handle the admin_init action.  All page handling is done on admin_init
     * so we have the opportunity to run code prior to WP rendering any output.
     *
     * @param string $page The name of the page to route to (e.g. "Index" or "Edit").
     * @param Response $response Inject a response object, usually for tests.
     *
     * @return \Dewdrop\Admin\ComponentAbstract
     */
    public function adminInit($page = null, Response $response = null)
    {
        if ($this->isCurrentlyActive()) {
            $page = $this->createPageObject($page);

            if (null === $response) {
                $response = new Response();
            }

            $response->setPage($page);
            $this->dispatchPage($page, $response);

            $this->response = $response;
        }

        return $this;
    }

    /**
     * Check to see if this component is currently being accessed.  We do this
     * manually because we want to know whether the component is in use before
     * WP would itself be able to tell us.  This allows us to dispatch pages on
     * admin_init, which is early enough in the process that we can easily enqueue
     * other resources.  Also, this gives us the chance to run code before WP has
     * rendered any output.
     *
     * @return boolean
     */
    protected function isCurrentlyActive()
    {
        return preg_match('/^' . $this->getSlug() . '($|\/)/i', $this->request->getQuery('page')) ||
            $this->getSlug() === $this->request->getPost('action');
    }

    /**
     * Route requests to this component to the specified in the "route"
     * parameter of the query string, if set.  This allows us to manage multiple
     * pages in a component without having to hook into WP again for every page.
     */
    public function route()
    {
        if ($this->response) {
            $this->response->render();
        }
    }

    /**
     * This is the callback we added to the "admin_menu" action in the
     * register() method.  It essentially tells WP to call this component's
     * route() method whenever the component is accessed.
     */
    public function registerMenuPage()
    {
        $slug = $this->getSlug();

        $this->addObjectPage(
            $this->title,
            $this->title,
            'add_users',
            $slug,
            array($this, 'route'),
            $this->icon,
            $this->menuPosition
        );

        if (count($this->submenuPages)) {
            global $submenu_file;

            foreach ($this->submenuPages as $page) {
                $url = $slug;

                if ('Index' !== $page['route']) {
                    $url .= '/' . $page['route'];
                }

                // If the current route matches the page linked then mark it as selected
                if ($url === $this->request->getQuery('page')) {
                    $submenu_file = $url;
                }

                $this->addSubmenuPage(
                    $slug,
                    $page['title'],
                    $page['title'],
                    'add_users',
                    $url,
                    array($this, 'route')
                );
            }
        }
    }

    /**
     * Get a URL for a page in this component.  This method will automatically
     * return submenu-friendly URLs when a submenu item matches the supplied
     * page and params arguments.
     *
     * @param string $page
     * @param array $params
     * @return string
     */
    public function url($page, array $params = array())
    {
        $base  = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . $this->getSlug();
        $query = $this->assembleQueryString($params);

        foreach ($this->submenuPages as $submenu) {
            if ($submenu['route'] === $page) {
                $submenuParams  = $subment['params'];
                $matchesSubmenu = true;

                foreach ($params as $name => $value) {
                    if (!isset($submenuParams[$name]) || $submenuParams['value'] !== $value) {
                        $matchesSubmenu = false;
                        break;
                    }
                }

                if ($matchesSubmenu) {
                    if ('Index' === $submenu['route']) {
                        $route = '';
                    } else {
                        $route = '/' . $submenu['route'];
                    }

                    return "{$base}{$route}{$query}";
                }
            }
        }

        return "{$base}&route={$page}{$query}";
    }

    /**
     * Add a link to the submenu for this component.
     *
     * @param string $title
     * @param string $page
     * @param array $params
     * @return \Dewdrop\Admin\ComponentAbstract
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
     * Get the DB adapter associated with this component.  This is used
     * frequently by page classes to get access to the DB connection.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getDb()
    {
        return $this->db;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPimple()
    {
        return $this->pimple;
    }

    /**
     * Set the title of this component as it should be displayed in the WP
     * admin menu.
     *
     * @param string $title
     * @return \Dewdrop\Admin\ComponentAbstract
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
     * @return \Dewdrop\Admin\ComponentAbstract
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set the position of the component's menu entry
     *
     * @see $menuPosition
     * @param integer $menuPosition
     * @return \Dewdrop\Admin\ComponentAbstract
     */
    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * Make sure the title property was set in the component's init()
     * method.
     *
     * @throws \Dewdrop\Exception
     */
    protected function checkRequiredProperties()
    {
        if (!$this->title) {
            throw new \Dewdrop\Exception('Component title is required');
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
     * @param PageAbstract $page
     * @param Response $response
     * @return void
     */
    protected function dispatchPage(PageAbstract $page, Response $response)
    {
        $page->init();

        if ($page->shouldProcess()) {
            $responseHelper = $page->createResponseHelper($this->redirector);

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
        $page->render();
        $output = ob_get_clean();

        // Automatically render view if no output is generated
        if (!$output) {
            $output = $page->renderView();
        }

        $response->setOutput($output);
    }

    /**
     * A mock wrapper for WP's add_object_page() function.  Allows calls
     * during testing without error.
     *
     * @return void
     */
    protected function addObjectPage()
    {
        if (function_exists('add_object_page')) {
            call_user_func_array('add_object_page', func_get_args());
        }
    }

    /**
     * A mock wrapper for WP's add_submenu_page() function.  Allows calls
     * during testing without error.
     *
     * @return void
     */
    protected function addSubmenuPage()
    {
        if (function_exists('add_submenu_page')) {
            call_user_func_array('add_submenu_page', func_get_args());
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
    protected function getSlug()
    {
        $fullClass = str_replace('\\', '/', get_class($this));
        $segments  = explode('/', $fullClass);
        $nameIndex = count($segments) - 2;

        return $segments[$nameIndex];
    }

    /**
     * Determine which page (e.g. Index, Edit, etc.) is currently being
     * displayed.  We take the route query parameter, subtract the slug
     * and see what's left, defaulting to "Index".
     *
     * @return string
     */
    private function determineCurrentPage()
    {
        $slug  = $this->getSlug();
        $page  = str_replace($slug, '', $this->request->getQuery('page'));
        $route = $this->request->getQuery('route', 'Index');

        if ($page) {
            return ltrim($page, '/');
        }

        return $route;
    }

    /**
     * Assemble the remainder of a URL query string.
     *
     * @param array $params
     * @return string
     */
    protected function assembleQueryString(array $params)
    {
        $segments = array();

        foreach ($params as $name => $value) {
            $segments[] = sprintf(
                "%s=%s",
                rawurlencode($name),
                rawurlencode($value)
            );
        }

        return (count($segments) ? '?' . implode('&', $segments) : '');
    }
}
