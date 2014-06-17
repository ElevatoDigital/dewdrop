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
use Dewdrop\Admin\Response;
use Dewdrop\View\View;
use Zend\View\Helper\HeadScript;

class Wp extends EnvAbstract
{
    private $output;

    public function renderLayout($content, HeadScript $headScript = null)
    {
        $view = new View();

        $output  = $view->wpWrap()->open();
        $output .= $content;
        $output .= $view->wpWrap()->close();

        return $output;
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
    public function url(ComponentAbstract $component, $page, array $params = array())
    {
        $base  = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . $component->getSlug();
        $query = $this->assembleQueryString($params, $separator = '&');

        foreach ($component->getSubmenuPages() as $submenu) {
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

    public function redirect($url)
    {
        wp_safe_redirect($url);
        exit;
    }

    /**
     * Add an "admin_menu" callback to let WP know that you want to register
     * an admin component.
     */
    public function initComponent(ComponentAbstract $component)
    {
        add_action(
            'admin_init',
            function () use ($component) {
                return $this->adminInit($component);
            }
        );

        add_action(
            'admin_menu',
            function () use ($component) {
                return $this->registerMenuPage($component);
            }
        );

        // Also allow routing via WP's ajax facility to avoid rendering layout
        add_action('wp_ajax_' . $component->getSlug(), array($this, 'route'));
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
    public function adminInit(ComponentAbstract $component, $page = null, Response $response = null)
    {
        if ($this->componentIsCurrentlyActive($component)) {
            $page = $component->createPageObject($component->getRequest()->getQuery('route', 'Index'));

            if (null === $response) {
                $response = new Response();
            }

            $response->setPage($page);
            $this->output = $component->dispatchPage($page, $response);
        }

        return $this;
    }

    /**
     * This is the callback we added to the "admin_menu" action in the
     * register() method.  It essentially tells WP to call this component's
     * route() method whenever the component is accessed.
     */
    public function registerMenuPage(ComponentAbstract $component)
    {
        $slug = $component->getSlug();

        $this->addObjectPage(
            $component->getTitle(),
            $component->getTitle(),
            'add_users',
            $component->getSlug(),
            array($this, 'route'),
            $component->getIcon(),
            $component->getMenuPosition()
        );

        if (count($component->getSubmenuPages())) {
            global $submenu_file;

            foreach ($component->getSubmenuPages() as $page) {
                $url = $slug;

                if ('Index' !== $page['route']) {
                    $url .= '/' . $page['route'];
                }

                // If the current route matches the page linked then mark it as selected
                if ($url === $component->getRequest()->getQuery('page')) {
                    $submenu_file = $url;
                }

                $this->addSubmenuPage(
                    $component->getSlug(),
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
     * Route requests to this component to the specified in the "route"
     * parameter of the query string, if set.  This allows us to manage multiple
     * pages in a component without having to hook into WP again for every page.
     */
    public function route()
    {
        echo $this->output;
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
    protected function componentIsCurrentlyActive(ComponentAbstract $component)
    {
        return preg_match('/^' . $component->getSlug() . '($|\/)/i', $component->getRequest()->getQuery('page')) ||
            $component->getSlug() === $component->getRequest()->getPost('action');
    }
}
