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
        require_once $libraryPath . '/Zend/Loader/AutoloaderFactory.php';

        $config = array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    'Dewdrop' => $libraryPath . '/Dewdrop',
                    'Zend'    => $libraryPath . '/Zend',
                )
            )
        );

        return Zend\Loader\AutoloaderFactory::factory($config);
    }
}
