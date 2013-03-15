<?php

namespace Dewdrop\Db;

use Dewdrop\Paths;
use Dewdrop\Exception;

/**
 * @package Dewdrop
 */
abstract class Table
{
    /**
     * @var \Dewdrop\Db\Adapter
     */
    private $db;

    /**
     * @var \Dewdrop\Paths
     */
    private $paths;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @param \Dewdrop\Db\Adapter $db
     */
    public function __construct(Adapter $db, Paths $paths = null)
    {
        $this->db    = $db;
        $this->paths = ($paths ?: new Paths());

        $this->init();

        if (!$this->tableName) {
            throw new Exception('You must call setTableName() in your init() method.');
        }
    }

    abstract public function init();

    /**
     * @param string $tableName
     * @returns \Dewdrop\Db\Table
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        if (!$this->metadata) {
            $metadataPath = "{$this->paths->getModels()}/metadata/{$this->tableName}.php";

            if (!file_exists($metadataPath)) {
                throw new Exception(
                    'Table metadata not found.  '
                    . 'Run "db-metadata" command to generate it.'
                );
            }

            $this->metadata = require $metadataPath;

            if (!is_array($this->metadata)) {
                throw new Exception(
                    'Failed to retrieve table metadata not found.  '
                    . 'Run "db-metadata" command to generate it.'
                );
            }
        }

        return $this->metadata;
    }
}
