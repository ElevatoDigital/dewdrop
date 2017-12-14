<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\MultiInstance;

use Dewdrop\Db\Adapter as DbAdapter;

/**
 * Class Instance
 *
 * @package Dewdrop\MultiInstance
 */
class Instance
{
    /**
     * @var DbAdapter
     */
    private $dbAdapter;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var array
     */
    private $metadata;

    /**
     * Instance constructor.
     *
     * @param Manager $manager
     * @param array $metadata
     */
    public function __construct(Manager $manager, array $metadata)
    {
        $this->manager  = $manager;
        $this->metadata = $metadata;
    }

    /**
     * @param $index
     * @return mixed
     * @throws Exception
     */
    public function get($index)
    {
        if (!array_key_exists($index, $this->metadata)) {
            throw new Exception("Index '{$index}' not present in instance metadata.");
        }

        return $this->metadata[$index];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getId()
    {
        return $this->get($this->manager->getIdColumn());
    }

    /**
     * @return $this
     */
    public function switchTo()
    {
        $this->manager->setCurrent($this);

        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getSubdomain()
    {
        return $this->get($this->manager->getSubdomainColumn());
    }

    /**
     * @return DbAdapter
     */
    public function getDbAdapter()
    {
        if (!$this->dbAdapter) {
            $this->dbAdapter = $this->manager->createAdapterForInstance($this);
        }

        return $this->dbAdapter;
    }

    /**
     * @return Dbdeploy
     * @throws Exception
     */
    public function dbdeploy()
    {
        $config         = $this->manager->getManageDbConfig();
        $config['name'] = $this->manager->getDatabaseNameForId($this->getId());

        return new Dbdeploy($config, $this->getDbAdapter());
    }
}
