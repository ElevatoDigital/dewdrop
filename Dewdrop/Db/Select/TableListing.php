<?php

namespace Dewdrop\Db\Select;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\FieldProvider\ProviderInterface;
use Dewdrop\Db\Select;
use Dewdrop\Db\Table;

class TableListing
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var DbAdapter
     */
    private $db;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @param array
     */
    private $referenceTitleColumns = [];

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->db    = $this->table->getAdapter();
    }

    public function setReferenceTitleColumn($foreignKeyColumn, $titleColumn)
    {
        $this->referenceTitleColumns[$foreignKeyColumn] = $titleColumn;

        return $this;
    }

    public function reset()
    {
        $this->select  = null;
        $this->aliases = [];
    }

    public function select()
    {
        if (!$this->select) {
            $this->select = $this->generateSelect();
        }

        return $this->select;
    }

    private function generateSelect()
    {
        $select = $this->table->select();

        $this->selectFromTable($select);
        $this->selectForeignKeyValues($select);
        $this->selectFieldProviderValues($select);

        return $select;
    }

    private function selectFromTable(Select $select)
    {
        return $select->from(
            [$this->getAlias($this->table->getTableName()) => $this->table->getTableName()]
        );
    }

    private function selectForeignKeyValues(Select $select)
    {
        $tableAlias = $this->getAlias($this->table->getTableName());

        foreach ($this->table->getMetadata('references') as $column => $reference) {
            $metadata = $this->table->getMetadata('columns', $column);

            if ($metadata['NULLABLE']) {
                $join = 'joinLeft';
            } else {
                $join = 'join';
            }

            $refTable = $reference['table'];
            $refAlias = $this->getAlias($refTable);

            $columnAlias = preg_replace('/_id$/i', '', $column);
            $titleColumn = $this->findReferenceTitleColumn($column, $reference);

            $select->$join(
                [$refAlias => $refTable],
                sprintf(
                    '%s = %s',
                    $this->db->quoteIdentifier("{$tableAlias}.{$column}"),
                    $this->db->quoteIdentifier("{$refAlias}.{$reference['column']}")
                ),
                [$columnAlias => $titleColumn]
            );
        }

        return $select;
    }

    private function selectFieldProviderValues(Select $select)
    {
        /* @var $provider ProviderInterface */
        foreach ($this->table->getFieldProviders() as $provider) {
            $provider->augmentSelect($select);
        }

        return $select;
    }

    private function getAlias($tableName)
    {
        if (array_key_exists($tableName, $this->aliases)) {
            return $this->aliases[$tableName];
        }

        $chars = 1;
        $alias = substr($tableName, 0, $chars);

        while (in_array($alias, $this->aliases)) {
            $chars += 1;
            $alias  = substr($tableName, 0, $chars);
        }

        $this->aliases[$tableName] = $alias;

        return $alias;
    }

    private function findReferenceTitleColumn($localColumn, array $reference)
    {
        if (array_key_exists($localColumn, $this->referenceTitleColumns)) {
            return $this->referenceTitleColumns[$localColumn];
        }

        $metadata = $this->db->getTableMetadata($reference['table']);
        $columns  = array_keys($metadata['columns']);

        if (in_array('name', $columns)) {
            return 'name';
        } elseif (in_array('title', $columns)) {
            return 'title';
        } else {
            return array_shift($columns);
        }
    }
}
