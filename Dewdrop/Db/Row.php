<?php

namespace Dewdrop\Db;

/**
 * The Row class provides a simple way to manipulate the values associated
 * with a single database row.
 *
 * You can set values on the row using the set() method like this:
 *
 * <code>
 * $row->set('column_name', $value);
 * </code>
 *
 * Or, you can set multiple values at once like this:
 *
 * <code>
 * $row->set(
 *     array(
 *         'column_name'   => $value,
 *         'second_column' => $anotherValue
 *     )
 * );
 * </code>
 *
 * Once the columns have been assigned the desired values, you can call save(),
 * which will either update or insert the row depending upon whether it already
 * exists in the database.  After saving, the row's data is automatically
 * refreshed to ensure it still accurately represents the corresponding row in
 * the database.  For example, if it is a new row, the primary key's value will
 * be populated after calling save().
 *
 * Additionally, you can retrieve field objects representing columns in this
 * row by calling its field() method:
 *
 * <code>
 * $row->field('column_name');
 * </code>
 *
 * The field object allows you to easily integrate with other Dewdrop
 * APIs, leveraging the database metadata to add validators, retrieve lists
 * of options, etc.
 */
class Row
{
    /**
     * The data represented by this row.
     *
     * @var array
     */
    private $data;

    /**
     * The table this row is associated with.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * The columns available to this row as defined by the associated table's
     * metadata.
     *
     * @var array
     */
    private $columns;

    /**
     * Instantiate the row, checking to ensure the data array contains only
     * those columns present in the table's metadata.
     *
     * @param \Dewdrop\Db\Table $table
     * @param array $data
     */
    public function __construct(Table $table, array $data = array())
    {
        $this->table   = $table;
        $this->data    = $data;
        $this->columns = $this->table->getMetadata('columns');

        // Unset any data values not present in the columns array
        foreach ($this->data as $column => $value) {
            if (!array_key_exists($column, $this->columns)) {
                unset($this->data[$column]);
            }
        }

        // Ensure each column is represented in the data array, even if null
        foreach ($this->columns as $column => $metadata) {
            if (!array_key_exists($column, $this->data)) {
                $this->data[$column] = null;
            }
        }
    }

    /**
     * Get the table associated with this row.
     *
     * @return \Dewdrop\Db\Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Retrieve a field object representing the specified column name.
     *
     * @param string $name
     * @return \Dewdrop\Db\Field
     */
    public function field($name)
    {
        $field = $this->table->field($name, $this);
        $field->setRow($this);
        return $field;
    }

    /**
     * The the value of one or more columns on this row.
     *
     * If $name is a string, that single column will be set.  If $name is an
     * array, the array will be iterated and each key-value pair will be
     * set on the row.
     *
     * @param string|array $column
     * @param mixed $value
     * @return \Dewdrop\Db\Row
     */
    public function set($column, $value)
    {
        if (!array_key_exists($column, $this->columns)) {
            throw new Exception("Setting value on invalid  column \"{$column}\"");
        }

        if (is_array($column)) {
            foreach ($columns as $key => $value) {
                $this->set($key, $value);
            }
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        $this->data[$column] = $value;

        return $this;
    }

    /**
     * Get the value of the specified column.
     *
     * @param string $column
     * @return mixed
     */
    public function get($column)
    {
        if (!array_key_exists($column, $this->columns)) {
            throw new Exception("Getting value of invalid  column \"{$column}\"");
        }

        return $this->data[$column];
    }

    /**
     * Save this row, inserting if it is a new row and updating otherwise.
     *
     * @return \Dewdrop\Db\Row
     */
    public function save()
    {
        if (!$this->isNew()) {
            $this->table->update($this->data, $this->assembleUpdateWhereClause());
        } else {
            $this->table->insert($this->data);

            // Set value of auto-incrementing primary key, if available
            foreach ($this->table->getMetadata('columns') as $column => $metadata) {
                if ($metadata['IDENTITY'] && $metadata['PRIMARY']) {
                    $this->set($column, $this->getTable()->getAdapter()->lastInsertId());
                }
            }
        }

        $this->refresh();

        return $this;
    }

    /**
     * Determine whether this row is new or already present in the DB by
     * checking if its primary key columns have a value assigned.
     *
     * @return boolean
     */
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

    /**
     * Refresh the row, pulling in the latest data from the DB.
     *
     * @return \Dewdrop\Db\Row
     */
    private function refresh()
    {
        $pkey = array();

        foreach ($this->table->getPrimaryKey() as $column) {
            $pkey[] = $this->get($column);
        }

        $this->data = $this->table->findRowRefreshData($pkey);

        return $this;
    }

    /**
     * Assemble the WHERE clause for the update method using the primary key
     * column's from the associated table.
     *
     * @return string
     */
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
