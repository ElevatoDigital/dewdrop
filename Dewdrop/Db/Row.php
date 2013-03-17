<?php

namespace Dewdrop\Db;

class Row
{
    private $data;

    private $table;

    private $columns;

    public function __construct(Table $table, array $data = array())
    {
        $this->table   = $table;
        $this->data    = $data;
        $this->columns = $this->table->getMetadata();

        foreach ($this->data as $column => $value) {
            if (!array_key_exists($column, $this->columns)) {
                unset($this->data[$column]);
            }
        }

        foreach ($this->columns as $column => $metadata) {
            if (!array_key_exists($column, $this->data)) {
                $this->data[$column] = null;
            }
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    public function field($name)
    {
        return $this->table->field($name, $this);
    }

    public function set($column, $value)
    {
        if (!array_key_exists($column, $this->columns)) {
            throw new Exception("Setting value on invalid  column \"{$column}\"");
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        $this->data[$column] = $value;

        return $this;
    }

    public function get($column)
    {
        if (!array_key_exists($column, $this->columns)) {
            throw new Exception("Getting value of invalid  column \"{$column}\"");
        }

        return $this->data[$column];
    }

    public function save()
    {
        if (!$this->isNew()) {
            $this->table->update($this->data, $this->assembleUpdateWhereClause());
        } else {
            $this->table->insert($this->data);

            // Set value of auto-incrementing primary key, if available
            foreach ($this->table->getMetadata() as $column => $metadata) {
                if ($metadata['IDENTITY'] && $metadata['PRIMARY']) {
                    $this->set($column, $this->getTable()->getAdapter()->lastInsertId());
                }
            }
        }

        $this->refresh();
    }

    public function isNew()
    {
        $pkey = $this->table->getPrimaryKey();

        foreach ($pkey as $column) {
            if ($this->data[$column]) {
                return false;
            }
        }

        return true;
    }

    private function refresh()
    {
        $pkey = array();

        foreach ($this->table->getPrimaryKey() as $column) {
            $pkey[] = $this->get($column);
        }

        $this->data = $this->table->findRowRefreshData($pkey);
    }

    private function assembleUpdateWhereClause()
    {
        $pkey  = $this->table->getPrimaryKey();
        $db    = $this->table->getAdapter();
        $where = array();

        foreach ($pkey as $column) {
            $quotedColumn = $db->quoteIdentifier($column);

            $where[] = $db->quoteInto("{$quotedColumn} = ?", $this->data[$column]);
        }

        return implode(' AND ', $where);
    }
}
