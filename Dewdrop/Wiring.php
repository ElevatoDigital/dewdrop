<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Autoloader;
use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Paths;

/**
 * This class ties WP to your plugin by registering the necessary hooks to
 * let WP know where to find your components, short codes, etc.  In kicking
 * things off for other parts of your Dewdrop-based plugin, this class also
 * servers as a form of "root" class, passing core resources like the
 * \Dewdrop\Db\Adapter to other areas.
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
     * The autoloader used for Dewdrop, Zend, and model class loading
     *
     * @var \Dewdrop\Autoloader
     */
    protected $autoloader;

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
     * @param boolean $autoRegister
     * @param \Dewdrop\Db\Adapter $db
     * @param \Dewdrop\Inflector $inflector
     * @param \Dewdrop\Autoloader $autoloader
     */
    public function __construct(
        $autoRegister = true,
        $db = null,
        $inflector = null,
        $autoloader = null
    ) {
        global $wpdb;

        require_once __DIR__ . '/Autoloader.php';

        $this->autoloader = ($autoloader ?: new Autoloader());
        $this->db         = ($db ?: new DbAdapter($wpdb));
        $this->inflector  = ($inflector ?: new Inflector());
        $this->paths      = new Paths();

        if ($autoRegister) {
            $this->autoRegisterAdminComponents();
        }
    }

    /**
     * Look in the default admin path for any admin components and register
     * them.
     *
     * @return void
     */
    public function autoRegisterAdminComponents()
    {
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
        $component = new $className($this->db, $this);

        $component->register();
    }
}
