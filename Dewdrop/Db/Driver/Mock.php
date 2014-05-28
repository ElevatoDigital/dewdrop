<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Driver;

use Dewdrop\Db\Adapter;

/**
 * A mock DB adpater driver for use during testing.  You can certainly
 * create a mock of a normal driver class, but this is a little easier
 * to use in some situations.
 */
class Mock implements DriverInterface
{
    /**
     * The adapter this driver is attached to.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $adapter;

    /**
     * Create new instance with the supplied adapter.
     *
     * @param \Dewdrop\Db\Adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Grab the actual DB connection object (nothing in the case of the mock driver).
     *
     * @return null
     */
    public function getConnection()
    {
        return null;
    }

    /**
     * Fetch all results for the supplied SQL statement.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @param string $fetchMode
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        return array();
    }

    /**
     * Fetch a single column of the results from the supplied SQL statement.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @return array
     */
    public function fetchCol($sql, $bind = array())
    {
        return array();
    }

    /**
     * Fetch a single scalar value from the results of the supplied SQL
     * statement.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @return mixed
     */
    public function fetchOne($sql, $bind = array())
    {
        return null;
    }

    /**
     * Run the supplied query, binding the supplied data to the statement
     * prior to execution.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @return mixed
     */
    public function query($sql, $bind = array())
    {
        return array();
    }

    /**
     * Get the last insert ID from \wpdb after performing an insert on a table
     * with an auto-incrementing primary key.
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return 1;
    }

    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return "`";
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        return array();
    }

    /**
     * Returns an associative array containing all the foreign key relationships
     * associated with the supplied table.
     *
     * The array has the following format:
     *
     * <code>
     * array(
     *     'column_name' => array(
     *         'table'  => 'foreign_table',
     *         'column' => 'foreign_column'
     *     )
     * )
     * </code>
     *
     * @param string $tableName
     * @return array
     */
    public function listForeignKeyReferences($tableName)
    {
        return array();
    }

    /**
     * Returns an associative array containing all the unique constraints on a table.
     *
     * The array has the following format:
     *
     * <code>
     * array(
     *     'key_name' => array(
     *         sequence_in_index => 'column_name'
     *     )
     * )
     * </code>
     *
     * @param string $tableName
     * @return array
     */
    public function listUniqueConstraints($tableName)
    {
        return array();
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @return array
     */
    public function describeTable($tableName)
    {
        return array();
    }
}
