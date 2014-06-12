<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Paths;
use Dewdrop\Pimple;

/**
 * This class ties WP to your plugin by registering the necessary hooks to
 * let WP know where to find your components, short codes, etc.  In kicking
 * things off for other parts of your Dewdrop-based plugin, this class also
 * servers as a form of "root" class, passing core resources like the
 * \Dewdrop\Db\Adapter to other areas.
 *
 * @deprecated
 */
class Wiring
{
    /**
     * The database adapter wrapping wpdb
     *
     * @var \Dewdrop\Db\Adapter
     */
    protected $db;

    /**
     * An instance of \Dewdrop\Inflector used to locate admin components by
     * inflecting their namespaces into folder names
     *
     * @var \Dewdrop\Inflector
     */
    protected $inflector;

    /**
     * Paths utility for finding our way around the plugin
     *
     * @var \Dewdrop\Paths
     */
    protected $paths;

    /**
     * If auto-register is active, then the wiring class will attempt to find
     * all the components to register by traversing the default Dewdrop plugin
     * paths for them, rather than requiring your to manually register admin
     * components, shortcodes, etc.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        // We use output buffering because otherwise WP will output before we can redirect, etc.
        if ('cli' !== php_sapi_name()) {
            ob_start();
        }

        $this->inflector = new Inflector();
        $this->paths     = (isset($options['paths']) ? $options['paths'] : new Paths());

        if (isset($options['db']) && $options['db']) {
            $this->db = $options['db'];
        } else {
            global $wpdb;
            $this->db = new DbAdapter($wpdb);
        }

        if (!isset($options['autoRegister']) || $options['autoRegister']) {
            $this->autoRegisterAdminComponents();
        }
    }

    /**
     * This method is primarily around to create a seam for testing the Wiring
     * component.  At runtime, it will just use the WP is_admin() function.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return is_admin();
    }

    /**
     * Look in the default admin path for any admin components and register
     * them.
     *
     * @return void
     */
    public function autoRegisterAdminComponents()
    {
        // Don't both registering components if we're not in the WP admin area
        if (!$this->isAdmin()) {
            return;
        }

        $path = $this->paths->getAdmin();
        $dir  = opendir($path);

        while ($folder = readdir($dir)) {
            if (0 !== strpos($folder, '.') && is_dir("{$path}/{$folder}")) {
                $this->registerAdminComponent($folder);
            }
        }
    }

    /**
     * Register the admin component located at $path.
     *
     * @param string $path
     */
    public function registerAdminComponent($path)
    {
        $componentPath  = $this->paths->getAdmin() . '/' . $path . '/Component.php';
        $className      = '\Admin\\' . $this->inflector->camelize($path) . '\Component';

        require_once $componentPath;
        $component = new $className(Pimple::getInstance());

        $component->register();
    }

    /**
     * Get a reference to the assigned DB adapter.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Get a reference to the assigned inflector.
     *
     * @return \Dewdrop\Inflector
     */
    public function getInflector()
    {
        return $this->inflector;
    }

    /**
     * Get a reference to the assigned paths object.
     *
     * @return \Dewdrop\Paths
     */
    public function getPaths()
    {
        return $this->paths;
    }
}
