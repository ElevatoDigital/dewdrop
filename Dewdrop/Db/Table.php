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
     * @var string
     */
    private $rowClass = '\Dewdrop\Db\Row';

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

    public function getPrimaryKey()
    {
        $columns = array();

        foreach ($this->getMetadata() as $column => $metadata) {
            if ($metadata['PRIMARY']) {
                $position  = $metadata['PRIMARY_POSITION'];

                $columns[$position] = $column;
            }
        }

        ksort($columns);

        return array_values($columns);
    }

    /**
     * Get the DB adapter associated with this table object.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getAdapter()
    {
        return $this->db;
    }

    /**
     * Create a new \Dewdrop\Db\Select object.
     *
     * @return \Dewdrop\Db\Select
     */
    public function select()
    {
        return $this->db->select();
    }

    /**
     * Insert a new row.
     *
     * Data should be supplied as key value pairs, with the keys representing
     * the column names.
     *
     * @param array $data
     * @return integer Number of affected rows.
     */
    public function insert(array $data)
    {
        return $this->db->insert($this->tableName, $data);
    }

    /**
     * Update an existing row.
     *
     * Data should be supplied as key value pairs, with the keys representing
     * the column names.  The where clause should be an already assembled
     * and quoted string.  It should not be prefixed with the "WHERE" keyword.
     *
     * @param array $data
     * @param string $where
     */
    public function update(array $data, $where)
    {
        return $this->db->update($this->tableName, $data, $where);
    }

    public function find()
    {
        return $this->fetchRow($this->assembleFindSql(func_get_args()));
    }

    public function findRowRefreshData(array $args)
    {
        return $this->db->fetchRow(
            $this->assembleFindSql($args),
            ARRAY_A
        );
    }

    public function createRow(array $data = array())
    {
        $className = $this->rowClass;
        return new $className($this, $data);
    }

    public function fetchRow($sql)
    {
        $className = $this->rowClass;
        $data      = $this->db->fetchRow($sql, ARRAY_A);

        return new $className($this, $data);
    }

    private function assembleFindSql(array $args)
    {
        $pkey = $this->getPrimaryKey();

        foreach ($pkey as $index => $column) {
            if (!isset($args[$index])) {
                $pkeyColumnCount = count($pkey);
                throw new Exception("You must specify a value for all {$pkeyColumnCount} primary key columns");
            }

            $column  = $this->db->quoteIdentifier($column);
            $where[] = $this->db->quoteInto("{$column} = ?", $args[$index]);
        }

        $sql = sprintf(
            'SELECT * FROM %s WHERE %s',
            $this->db->quoteIdentifier($this->tableName),
            implode(' AND ', $where)
        );

        return $sql;
    }
}
