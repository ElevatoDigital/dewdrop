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
use Zend\View\Helper\HeadLink;
use Zend\View\Helper\HeadScript;

/**
 * This interface has to be implemented by an environment wanting to run
 * Dewdrop admin components.  So far, we've only attempted to support WP
 * and Silex, but it should be possible for other environments to drive
 * components if they implement this interface.
 */
interface EnvInterface
{
    /**
     * Register all the components that can be found in the specified path.
     * If no path is given, we'll use the getAdmin() method from \Dewdrop\Paths.
     *
     * @param string $path
     * @return EnvInterface
     */
    public function registerComponentsInPath($path = null);

    /**
     * Register the component located in the specified folder.  We expect a
     * Component.php file to be located in the folder that is a sub-class of
     * \Dewdrop\Admin\Component\ComponentAbstract.
     *
     * @param string $folder
     * @param string $classPrefix
     * @return EnvInterface
     */
    public function registerComponentFolder($folder, $classPrefix);

    /**
     * Register an already instantiated component.
     *
     * @param ComponentInterface $component
     * @return EnvInterface
     */
    public function registerComponent(ComponentInterface $component);

    /**
     * Render the layout for the response.  A layout should wrap the
     * page-specific content.  The HeadScript helper is also provided from the
     * page's view so that its contents can be integrated with the environment.
     *
     * @param string $content
     * @param HeadScript $headScript
     * @param HeadLink $headLink
     * @return string
     */
    public function renderLayout($content, HeadScript $headScript = null, HeadLink $headLink = null);

    /**
     * Return a URL that will work to route the user to the specified page in
     * your environment.  The URLs may vary quite a lot depending upon the
     * environment.
     *
     * @param ComponentInterface $component
     * @param string $page
     * @param array $params
     * @return string
     */
    public function url(ComponentInterface $component, $page, array $params = array());

    /**
     * Initialize the component, setting up any needed routes, event handlers,
     * etc. for the environment.
     *
     * @param ComponentInterface $component
     * @return void
     */
    public function initComponent(ComponentInterface $component);

    /**
     * Retrieve a component by name.
     *
     * @param string $name
     * @return ComponentInterface
     */
    public function getComponent($name);

    /**
     * Perform a redirect to the supplied URL using whatever method is preferred
     * by the current environment.
     *
     * @param string $url
     * @return mixed
     */
    public function redirect($url);
}
