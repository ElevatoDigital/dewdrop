<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db;

use Dewdrop\Exception;

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
     * A map of many-to-many and EAV fieldsthat tracks whether their initial
     * value has been loaded yet.  If not, the first time get() is called on
     * this row for the field, the value will be loaded from the database.
     *
     * @var array
     */
    private $virtualFieldsInitialized = array();

    /**
     * Instantiate the row, checking to ensure the data array contains only
     * those columns present in the table's metadata.
     *
     * @param Table $table
     * @param array $data
     */
    public function __construct(Table $table, array $data = array())
    {
        $this->table   = $table;
        $this->data    = $data;
        $this->columns = $this->table->getRowColumns();

        // Unset any data values not present in the columns array
        foreach ($this->data as $column => $value) {
            if (!in_array($column, $this->columns)) {
                unset($this->data[$column]);
            }

            if ($this->table->hasManyToManyRelationship($column)) {
                $this->virtualFieldsInitialized[] = $column;
            } elseif ($this->table->hasEav() && $this->table->getEav()->hasAttribute($column)) {
                $this->virtualFieldsInitialized[] = $column;
            }
        }

        // Ensure each column is represented in the data array, even if null
        foreach ($this->columns as $column) {
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
    public function set($column, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->set($key, $value);
            }

            return $this;
        }

        if (!in_array($column, $this->columns)) {
            throw new Exception("Setting value on invalid  column \"{$column}\"");
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
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (!in_array($name, $this->columns)) {
            throw new Exception("Getting value of invalid  column \"{$name}\"");
        }

        if ($this->table->hasManyToManyRelationship($name)
            && !in_array($name, $this->virtualFieldsInitialized)
        ) {
            $relationship = $this->table->getManyToManyRelationship($name);

            $this->virtualFieldsInitialized[] = $name;

            $this->data[$name] = $relationship->loadInitialValue($this);
        }

        if ($this->table->hasEav()
            && $this->table->getEav()->hasAttribute($name)
            && !in_array($name, $this->virtualFieldsInitialized)
        ) {
            $this->virtualFieldsInitialized[] = $name;

            $this->data[$name] = $this->table->getEav()->loadInitialValue($this, $name);
        }

        return $this->data[$name];
    }

    /**
     * Save this row, inserting if it is a new row and updating otherwise.
     *
     * @return \Dewdrop\Db\Row
     */
    public function save()
    {
        if (!$this->isNew()) {
            $updateData = $this->data;

            foreach ($this->table->getMetadata('columns') as $column => $metadata) {
                if ($metadata['IDENTITY'] && $metadata['PRIMARY']) {
                    unset($updateData[$column]);
                }
            }

            $this->table->update($updateData, $this->assembleUpdateWhereClause());
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
     * Deletes existing rows.
     *
     * @return int The number of rows deleted.
     */
    public function delete()
    {
        $where = $this->assembleUpdateWhereClause();

        /**
         * Execute the DELETE (this may throw an exception)
         */
        $result = $this->getTable()->delete($where);

        /**
         * Reset all fields to null to indicate that the row is not there
         */
        $this->data = array_combine(
            array_keys($this->data),
            array_fill(0, count($this->data), null)
        );

        return $result;
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
