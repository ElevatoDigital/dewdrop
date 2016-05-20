<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db;

use ArrayAccess;
use Dewdrop\Exception;
use Dewdrop\SaveHandlerInterface;

/**
 * The Row class provides a simple way to manipulate the values associated
 * with a single database row.
 *
 * You can set values on the row using the set() method like this:
 *
 * <pre>
 * $row->set('column_name', $value);
 * </pre>
 *
 * Or, you can set multiple values at once like this:
 *
 * <pre>
 * $row->set(
 *     array(
 *         'column_name'   => $value,
 *         'second_column' => $anotherValue
 *     )
 * );
 * </pre>
 *
 * Setting and retrieving values can also be done via direct object or array-style
 * use of the object:
 *
 * <pre>
 * // Access a column's value with object syntax
 * echo $row->column_name;
 *
 * // Access a column's value with array syntax
 * echo $row['column_name'];
 *
 * // Set a column's value with object syntax
 * $row->column_name = 'Value';
 *
 * // Set a column's value with array syntax
 * $row['column_name'] = 'Value';
 * </pre>
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
 * <pre>
 * $row->field('column_name');
 * </pre>
 *
 * The field object allows you to easily integrate with other Dewdrop
 * APIs, leveraging the database metadata to add validators, retrieve lists
 * of options, etc.
 */
class Row implements ArrayAccess, SaveHandlerInterface
{
    /**
     * The data represented by this row.
     *
     * @var array
     */
    protected $data;

    /**
     * The columns available to this row as defined by the associated table's
     * metadata.
     *
     * @var array
     */
    protected $columns;

    /**
     * The table this row is associated with.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

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

        // Apply defaults for new rows
        if (!count($this->data)) {
            foreach ($this->columns as $column) {
                $columnMetadata = $this->table->getMetadata('columns', $column);
                $default        = $columnMetadata['DEFAULT'];

                // We skip temporal fields to avoid problems with certain defaults (e.g., CURRENT_TIMESTAMP, NOW())
                if (!in_array($columnMetadata['GENERIC_TYPE'], ['date', 'time', 'timestamp']) && null !== $default) {
                    $this->data[$column] = $default;
                }
            }
        }

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

        $this->init();
    }

    /**
     * This method can be used by sub-classes to store initial values (for comparison
     * during saving, for example).
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * Provide a new table instance for the row.  Mostly useful if you're
     * implementing a __wakeup() method.
     *
     * @param Table $table
     * @return Row
     */
    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
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
     * Allow setting of data properties on this row via direct object property
     * syntax:
     *
     * <pre>
     * $row->property_id = $value;
     * </pre>
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Allow setting of data properties on this row via direct array syntax:
     *
     * <pre>
     * $row['property_id'] = $value;
     * </pre>
     *
     * This is part of the ArrayAccess interface built into PHP.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
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
     * @return Row
     * @throws Exception
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

        if ($this->table->hasEav() && $this->table->getEav()->hasAttribute($column)) {
            $this->virtualFieldsInitialized[] = $column;
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        $this->data[$column] = $value;

        return $this;
    }

    /**
     * Allow retrieval of data values via direct object property access:
     *
     * <pre>
     * echo $row->property_id;
     * </pre>
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Allow retrieval of data values via direct array access:
     *
     * <pre>
     * echo $row['property_id'];
     * </pre>
     *
     * This method is part of the ArrayAccess interface built into PHP.
     *
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Get the value of the specified column.
     *
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function get($name)
    {
        if (!in_array($name, $this->columns)) {
            throw new Exception("Getting value of invalid  column \"{$name}\"");
        }

        if ($this->table->hasManyToManyRelationship($name)
            && !in_array($name, $this->virtualFieldsInitialized)
            && !isset($this->data[$name])
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

        return (isset($this->data[$name]) ? $this->data[$name] : null);
    }

    /**
     * Test to see if a given column exists on this row using object syntax:
     *
     * <pre>
     * isset($row->column_name);
     * </pre>
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Test to see if a given column exists on this row using array syntax:
     *
     * <pre>
     * isset($row['column_name']);
     * </pre>
     *
     * This method is part of the ArrayAccess interface built into PHP.
     *
     * @param string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Test to see if the given column exists on this row.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name)
    {
        return in_array($name, $this->columns);
    }

    /**
     * We do not allow unsetting columns on a row.
     *
     * @param string $key
     * @throws \Dewdrop\Exception
     */
    public function __unset($key)
    {
        throw new Exception('Cannot unset columns on a Row object');
    }

    /**
     * Even though this method is required by the ArrayAccess interface,
     * we do not allow unsetting columns on a row.
     *
     * @param string $key
     * @throws \Dewdrop\Exception
     */
    public function offsetUnset($key)
    {
        throw new Exception('Cannot unset columns on a Row object');
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

            $this->table->update($updateData, $this->assembleUpdateWhereClause());
        } else {
            $id = $this->table->insert($this->data);

            // Set value of auto-incrementing primary key, if available
            if ($id) {
                $this->set(
                    current($this->table->getPrimaryKey()),
                    $id
                );
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
            if (isset($this->data[$column]) && $this->data[$column]) {
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
     * Returns the row data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns an array representation of the row data.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getData();
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

        // Virtual fields need to load their values again after data refresh
        $this->virtualFieldsInitialized = array();

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
