<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Env;

use Dewdrop\Pimple;
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
            $this->registerComponent($folder);
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
     * @return EnvAbstract
     */
    public function registerComponent($folder)
    {
        require_once $folder . '/Component.php';
        $componentName = basename($folder);
        $className     = '\Admin\\' . Pimple::getResource('inflector')->camelize($componentName) . '\Component';

        $component = new $className(Pimple::getInstance());

        $this->initComponent($component);

        $this->components[] = $component;

        return $this;
    }

    /**
     * Assemble the remainder of a URL query string.
     *
     * @param array $params
     * @param string $separator
     * @return string
     */
    protected function assembleQueryString(array $params, $separator)
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
