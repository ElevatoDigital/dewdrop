<?php

namespace Dewdrop;

use Zend;
use Dewdrop\Db\Adapter as DbAdapter;

class Wiring
{
    protected $db;

    protected $inflector;

    protected $autoloader;

    public function __construct(
        $autoRegister = true,
        $db = null,
        $libraryPath = null,
        $inflector = null,
        $autoloader = null
    ) {
        global $wpdb;

        if (null === $libraryPath) {
            $libraryPath = dirname(__DIR__);
        }

        $this->autoloader = ($autoloader ?: $this->buildAutoloader($libraryPath));
        $this->db         = ($db ?: new DbAdapter($wpdb));
        $this->inflector  = ($inflector ?: new Inflector());

        if ($autoRegister) {
            $this->autoRegisterAdminComponents();
        }
    }

    public function autoRegisterAdminComponents()
    {
        $path = __DIR__ . '/../../admin';
        $dir  = opendir($path);

        while ($folder = readdir($dir)) {
            if (0 !== strpos($folder, '.') && is_dir("{$path}/{$folder}")) {
                $this->registerAdminComponent($folder);
            }
        }
    }

    public function registerAdminComponent($path)
    {
        $componentPath  = $this->inflector->getComponentClassPath($path);
        $className      = $this->inflector->getComponentClass($path);

        require_once $componentPath;
        $component = new $className($this->db, $this);

        $component->register();
    }

    public function getModel($name)
    {
        require_once $this->inflector->getModelClassPath($name);
        $className = $this->inflector->getModelClass($name);
        return new $className($this->db);
    }

    protected function buildAutoloader($libraryPath)
    {
        require_once __DIR__ . '/Autoloader.php';
        return new \Dewdrop\Autoloader($libraryPath);
    }
}
