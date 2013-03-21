<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin;

use ReflectionClass;
use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Request;

/**
 * This class enables you to define how your component should appear and wire
 * into the WP admin shell.  For example, the $title property determines what
 * your plugin will be labeled in the admin navigation and the addToSubmenu()
 * method will allow you to add submenu items for your component.
 */
abstract class ComponentAbstract
{
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
     * An array of submenu pages that have been added by calling addToSubmenu().
     * These are actually tied in by registerMenuPage() after the admin_menu
     * hook is triggered.
     *
     * @var array
     */
    private $submenuPages = array();

    /**
     * A request object that makes it easier to work with GET and POST
     *
     * @var \Dewdrop\Request
     */
    private $request;

    /**
     * Create a component instance using the DB adapter creating by the Wiring
     * class.
     *
     * @param Adapter $db
     * @param Paths $paths
     * @param Request $request
     */
    public function __construct(Adapter $db, Paths $paths, Request $request = null)
    {
        $this->db      = $db;
        $this->paths   = $paths;
        $this->request = ($request ?: new Request());

        $this->init();

        $this->checkRequiredProperties();
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
        add_action('admin_menu', array($this, 'registerMenuPage'));
    }

    /**
     * Route requests to this component to the specified in the "route"
     * parameter of the query string, if set.  This allows us to manage multiple
     * pages in a component without having to hook into WP again for every page.
     */
    public function route()
    {
        $reflectedClass = new ReflectionClass($this);

        $pageKey   = $this->determineCurrentPage();
        $pageFile  = dirname($reflectedClass->getFileName()) . '/' . $pageKey . '.php';
        $className = $reflectedClass->getNamespaceName() . '\\' . $pageKey;

        require_once $pageFile;
        $page = new $className($this, $pageFile);

        $this->dispatchPage($page);
    }

    /**
     * This is the callback we added to the "admin_menu" action in the
     * register() method.  It essentially tells WP to call this component's
     * route() method whenever the component is accessed.
     */
    public function registerMenuPage()
    {
        $slug = $this->getSlug();

        add_object_page(
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

                add_submenu_page(
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
            'params' => $params
        );

        return $this;
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
     */
    protected function dispatchPage(PageAbstract $page)
    {
        $page->init();

        if ($page->shouldProcess()) {
            $responseHelper = $page->createResponseHelper();
            $page->process($responseHelper);
            $responseHelper->execute();
        }

        ob_start();
        $page->render();
        $output = ob_get_clean();

        // Automatically render view if no output is generated
        if (!$output) {
            $page->renderView();
        }
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
     * Get WP slug for this component.
     *
     * We use the classname, with namespace back slashes replaced with
     * URL-friendly front slashes, as the slug.
     *
     * @return string
     */
    private function getSlug()
    {
        return str_replace('\\', '/', get_class($this));
    }

    /**
     * Assemble the remainder of a URL query string.  We can assume that a
     * query string already exists because the "page" variable must be set
     * to get to this component in the first place, so this method's return
     * value is always prefixed with "&" to join it to the existing value.
     *
     * @param array $params
     * @return string
     */
    private function assembleQueryString(array $params)
    {
        $segments = array();

        foreach ($params as $name => $value) {
            $segments[] = sprintf(
                "%s=%s",
                rawurlencode($name),
                rawurlencode($value)
            );
        }

        return (count($segments) ? '&' . implode('&', $segments) : '');
    }
}
