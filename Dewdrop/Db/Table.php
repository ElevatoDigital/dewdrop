<?php

namespace Dewdrop\Db;

use Dewdrop\Paths;
use Dewdrop\Exception;
use Dewdrop\Db\Row;
use Dewdrop\Db\Field;

/**
 * @package Dewdrop
 */
abstract class Table
{
    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var array
     */
    private $fieldCustomizationCallbacks = array();

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
     * @var string
     */
    private $pluralTitle;

    /**
     * @var string
     */
    private $singularTitle;

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
     * Retrieve the field object associated with the specified name.
     *
     * @param string $name
     * @return \Dewdrop\Db\Field
     */
    public function field($name, Row $row = null)
    {
        if (isset($this->fields[$name])) {
            $field = $this->fields[$name];

            if ($row) {
                $field->setRow($row);
            }

            return $field;
        }

        $meta = $this->getMetadata('columns');

        if (!isset($meta[$name])) {
            throw new Exception("Attempting to retrieve unknown column \"{$name}\"");
        }

        $field = new Field($this, $name, $meta[$name]);

        if ($row) {
            $field->setRow($row);
        }

        if (isset($this->fieldCustomizationCallbacks[$name])) {
            call_user_func($this->fieldCustomizationCallbacks[$name], $field);
        }

        return $field;
    }

    /**
     * Assign a callback that will allow you to further customize a field
     * object whenever that object is requested using the table's field()
     * method.
     *
     * @param string $name
     * @param mixed $callback
     * @return \Dewdrop\Db\Table
     */
    public function customizeField($name, $callback)
    {
        $meta = $this->getMetadata('columns');

        if (!isset($meta[$name])) {
            throw new Exception("Setting customization callback for unknown column \"{$name}\"");
        }

        $this->fieldCustomizationCallbacks[$name] = $callback;

        return $this;
    }

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
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    public function setSingularTitle($singularTitle)
    {
        $this->singularTitle = $singularTitle;

        return $this;
    }

    public function getSingularTitle()
    {
        if (!$this->singurlarTitle) {
            $this->singularTitle = $this->getMetadata('titles', 'singular');
        }

        return $this->singularTitle;
    }

    public function setPluralTitle($pluralTitle)
    {
        $this->pluralTitle = $pluralTitle;

        return $this;
    }

    public function getPluralTitle()
    {
        if (!$this->pluralTitle) {
            $this->pluralTitle = $this->getMetadata('titles', 'plural');
        }

        return $this->pluralTitle;
    }

    /**
     * @return array
     */
    public function getMetadata($section = null, $index = null)
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

        if ($section && $index) {
            return $this->metadata[$section][$index];
        } elseif ($section) {
            return $this->metadata[$section];
        } else {
            return $this->metadata;
        }
    }

    public function getPrimaryKey()
    {
        $columns = array();

        foreach ($this->getMetadata('columns') as $column => $metadata) {
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
