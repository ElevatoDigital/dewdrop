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
use Dewdrop\Exception;
use Dewdrop\Pimple;
use Dewdrop\Session;
use DirectoryIterator;

/**
 * This class provides a couple methods that are common to all the admin
 * environments Dewdrop works with.
 */
abstract class EnvAbstract implements EnvInterface
{
    /**
     * The registered \Dewdrop\Admin\ComponentAbstract objects.
     *
     * @var array
     */
    protected $components = array();

    /**
     * A Dewdrop Session object useful for get flash messenger messages, etc.
     *
     * @var Session
     */
    protected $session;

    /**
     * The registered active component object.
     * @var ComponentInterface
     */
    protected $activeComponent;

    /**
     * The core client-side dependencies we expect to be loaded in the admin.
     * These should all be sent through the bowerUrl() view helper so that their
     * paths point to the bower_components folder for the current environment.
     *
     * @var array
     */
    protected $coreClientSideDependencies = [
        'js' => [
            'modernizr'    => '/dewdrop/www/js/modernizr.custom.63049.js',
            'jquery'       => '/jquery/dist/jquery.min.js',
            'bootstrap'    => '/bootstrap/dist/js/bootstrap.min.js',
            'keymaster'    => '/keymaster/keymaster.js',
            'moment'       => '/moment/min/moment-with-locales.min.js',
            'velocity'     => '/velocity/jquery.velocity.min.js',
            'velocity-ui'  => '/velocity/velocity.ui.js',
            'underscore'   => '/underscore/underscore.js',
            'backbone'     => '/backbone/backbone.js',
            'requirejs'    => '/requirejs/require.js',
            'dewdrop-core' => '/dewdrop/www/js/core.js',
            'dewdrop-form' => '/dewdrop/www/js/form.js',
        ],
        'css' => [
            'bootstrap'     => '/bootstrap/dist/css/bootstrap.min.css',
            'dewdrop-admin' => '/dewdrop/www/css/admin.css'
        ]
    ];

    /**
     * Prepend a client-side dependency you'd like to use throughout the admin environment.
     *
     * @param string $type Either "css" or "js".
     * @param string $name An identifier for the dependency.
     * @param string $path The path (in your bower_components folder) to the dependency.
     * @return $this
     */
    public function prependClientSideDependency($type, $name, $path)
    {
        $this->validateClientSideDependencyType($type);

        $this->coreClientSideDependencies[$type] = array_merge(
            array($name => $path),
            $this->coreClientSideDependencies[$type]
        );

        return $this;
    }

    /**
     * Append a client-side dependency you'd like to use throughout the admin environment.
     *
     * @param string $type Either "css" or "js".
     * @param string $name An identifier for the dependency.
     * @param string $path The path (in your bower_components folder) to the dependency.
     * @return $this
     */
    public function appendClientSideDependency($type, $name, $path)
    {
        $this->validateClientSideDependencyType($type);

        $this->coreClientSideDependencies[$type][$name] = $path;

        return $this;
    }

    /**
     * Add a client-side dependency you'd like to use throughout the admin environment.
     *
     * @param string $type Either "css" or "js".
     * @param string $name An identifier for the dependency.
     * @param string $path The path (in your bower_components folder) to the dependency.
     * @param string $key The key of the value you want to put a dependency after.
     * @return $this
     */
    public function addClientSideDependencyAfterKey($type, $name, $path, $key)
    {
        $this->validateClientSideDependencyType($type);

        $dependenciesOfType = $this->coreClientSideDependencies[$type];

        $offset = array_search($key, array_keys($dependenciesOfType)) + 1;
        $lastIndex = count($dependenciesOfType) - 1;

        $this->coreClientSideDependencies[$type] = array_slice($dependenciesOfType, 0, $offset, true)
                                                    + [$name => $path]
                                                    + array_slice($dependenciesOfType, $offset, $lastIndex, true);

        return $this;
    }

    /**
     * Validate that a type is either "css" or "js".
     *
     * @param string $type Either "css" or "js".
     */
    private function validateClientSideDependencyType($type)
    {
        if (!array_key_exists($type, $this->coreClientSideDependencies)) {
            throw new Exception('Client-side dependencies must be of type "css" or "js".');
        }
    }

    /**
     * Inflect a component name for use in URLs and routes.
     *
     * @param string $name
     * @return string
     */
    public function inflectComponentName($name)
    {
        return $name;
    }

    /**
     * Inject a Session object.  If you don't provide one, we'll locate it with
     * Pimple, but it's useful to inject during testing.
     *
     * @param Session $session
     * @return $this
     */
    public function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Set a reference to the active component.
     *
     * @param ComponentInterface $component
     * @return $this
     */
    public function setActiveComponent(ComponentInterface $component)
    {
        $this->activeComponent = $component;

        return $this;
    }

    /**
     * Get a reference to the active component.
     *
     * @return ComponentInterface
     */
    public function getActiveComponent()
    {
        return $this->activeComponent;
    }

    /**
     * Look for and register all admin components in the given path.  If
     * no path is provided, the \Dewdrop\Paths->getAdmin() method will be
     * used to find the default admin path for the application.
     *
     * @param string $path
     * @return EnvAbstract
     */
    public function registerComponentsInPath($path = null)
    {
        if (null === $path) {
            $path = Pimple::getResource('paths')->getAdmin();
        }

        $adminFolders     = new DirectoryIterator($path);
        $componentFolders = array();

        foreach ($adminFolders as $folder) {
            if (0 === strpos($folder, '.')) {
                continue;
            }

            if (is_dir($path . '/' . $folder)) {
                $componentFolders[] = $path . '/'. $folder;
            }
        }

        foreach ($componentFolders as $folder) {
            $this->registerComponentFolder($folder);
        }

        return $this;
    }

    /**
     * Register the single admin component located in the supplied path.  This
     * can be useful if you want to register individual components that are
     * outside your normal folder for admin components.  For example, if you've
     * got some reuseable admin components in a library, or Dewdrop itself, you
     * could register them with this method.
     *
     * @param string $folder
     * @param string $classPrefix
     * @return EnvAbstract
     */
    public function registerComponentFolder($folder, $classPrefix = '\Admin\\')
    {
        require_once $folder . '/Component.php';
        $componentName = basename($folder);
        $className     = $classPrefix . Pimple::getResource('inflector')->camelize($componentName) . '\Component';

        $component = new $className(Pimple::getInstance());

        return $this->registerComponent($component);
    }

    /**
     * Register an already instantiated component.
     *
     * @param ComponentInterface $component
     * @return EnvAbstract
     */
    public function registerComponent(ComponentInterface $component)
    {
        $this->initComponent($component);

        $this->components[] = $component;

        return $this;
    }

    /**
     * Retrieve a component by name.
     *
     * @param string $name
     * @return ComponentInterface
     */
    public function getComponent($name)
    {
        /* @var $component ComponentInterface */
        foreach ($this->components as $component) {
            if ($name === $component->getName()) {
                return $component;
            }
        }

        return null;
    }

    /**
     * Assemble the remainder of a URL query string.
     *
     * @param array $params
     * @param string $separator
     * @return string
     */
    public function assembleQueryString(array $params, $separator)
    {
        $segments = array();

        foreach ($params as $name => $value) {
            $segments[] = sprintf(
                "%s=%s",
                rawurlencode($name),
                rawurlencode($value)
            );
        }

        return (count($segments) ? $separator . implode('&', $segments) : '');
    }
}
