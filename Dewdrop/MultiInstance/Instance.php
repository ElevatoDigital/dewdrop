<?php

namespace Dewdrop\MultiInstance;

use Dewdrop\Db\Adapter as DbAdapter;

class Instance
{
    /**
     * @var
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

    public function __construct(Manager $manager, array $metadata)
    {
        $this->manager  = $manager;
        $this->metadata = $metadata;
    }

    public function get($index)
    {
        if (!array_key_exists($index, $this->metadata)) {
            throw new Exception("Index '{$index}' not present in instance metadata.");
        }

        return $this->metadata[$index];
    }

    public function getId()
    {
        return $this->get($this->manager->getIdColumn());
    }

    public function switchTo()
    {
        $this->manager->setCurrent($this);

        return $this;
    }

    public function getSubdomain()
    {
        return $this->get($this->manager->getSubdomainColumn());
    }

    public function getDbAdapter()
    {
        if (!$this->dbAdapter) {
            $this->dbAdapter = $this->manager->createAdapterForInstance($this);
        }

        return $this->dbAdapter;
    }
}