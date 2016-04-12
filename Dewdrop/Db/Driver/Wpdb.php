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
use Dewdrop\Db\Select;
use Dewdrop\Exception;
use wpdb as RawWpdb;

/**
 * DB driver using wpdb for all MySQL access.  This allows us to run Dewdrop in
 * a WordPress enironment without creating an additional database connection.
 */
class Wpdb implements DriverInterface
{
    /**
     * The adapter this driver is attached to.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $adapter;

    /**
     * @var \mysqli
     */
    protected $mysqli;

    /**
     * The wpdb object itself.
     *
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Create new driver using the supplied adapter and wpdb object.  If
     * $wpdb is not supplied, we'll attempt to grab the instance created
     * by WordPress itself from the global scope (ugh).
     *
     * @param \Dewdrop\Db\Adapter $adapter
     * @param \wpdb $wpdb
     * @throws Exception
     */
    public function __construct(Adapter $adapter, RawWpdb $wpdb = null)
    {
        if (!$wpdb) {
            global $wpdb;

            if (!isset($wpdb) || !$wpdb instanceof RawWpdb) {
                throw new Exception('Could not find wpdb');
            }
        }

        $this->adapter = $adapter;
        $this->wpdb    = $wpdb;

        // Suppress wpdb's own error display do we can do our own
        $this->wpdb->suppress_errors = true;
    }

    /**
     * Retrieve the raw wpdb object to use it directly.
     *
     * @return \wpdb
     */
    public function getConnection()
    {
        return $this->wpdb;
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
        if (null === $fetchMode) {
            $fetchMode = ARRAY_A;
        }

        $sql = $this->adapter->prepare($sql, $bind);
        $rs  = $this->execWpdb($this->wpdb->get_results($sql, $fetchMode));

        return $rs;
    }

    /**
     * Fetch all results for the supplied SQL query using a PHP generator.
     *
     * This approach uses less memory, but the result set has a forward-only cursor.
     *
     * The SQL query can be a simple string or a Select object.  The bind array
     * should supply values for all the parameters, either named or numeric, in
     * the query.  And the fetch mode should match one of these 4 class constants
     * from \Dewdrop\Db\Adapter: ARRAY_A, ARRAY_N, OBJECT, or OBJECT_K.
     *
     * @param string|Select $sql
     * @param array $bind
     * @param string $fetchMode
     * @return \Generator
     * @throws Exception
     */
    public function fetchAllWithGenerator($sql, $bind = [], $fetchMode = null)
    {
        if (null === $fetchMode) {
            $fetchMode = Adapter::ARRAY_A;
        }

        $mysqli = $this->getMysqli();
        $sql    = $this->adapter->prepare($sql, $bind);

        /* @var \mysqli_result $mysqliResult */
        $mysqliResult = mysqli_query($mysqli, $sql);

        if (!$mysqliResult) {
            throw new Exception("{$mysqli->errno}: {$mysqli->error}");
        }

        switch ($fetchMode) {
            case Adapter::ARRAY_A:
                $methodName = 'fetch_assoc';
                break;
            case Adapter::ARRAY_N:
                $methodName = 'fetch_row';
                break;
            case Adapter::OBJECT: // intentional fall-through
            case Adapter::OBJECT_K:
                $methodName = 'fetch_object';
                break;
            default:
                throw new Exception("Unsupported fetch mode '{$fetchMode}'");
        }

        while (null !== ($row = $mysqliResult->$methodName())) {
            yield $row;
        }
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
        return $this->execWpdb($this->wpdb->get_col($this->adapter->prepare($sql, $bind)));
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
        return $this->execWpdb($this->wpdb->get_var($this->adapter->prepare($sql, $bind)));
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
        return $this->execWpdb($this->wpdb->query($this->adapter->prepare($sql, $bind)));
    }

    /**
     * Get the last insert ID from \wpdb after performing an insert on a table
     * with an auto-incrementing primary key.
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->wpdb->insert_id;
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
        return $this->fetchCol('SHOW TABLES');
    }

    /**
     * Returns an associative array containing all the foreign key relationships
     * associated with the supplied table.
     *
     * The array has the following format:
     *
     * <pre>
     * array(
     *     'column_name' => array(
     *         'table'  => 'foreign_table',
     *         'column' => 'foreign_column'
     *     )
     * )
     * </pre>
     *
     * @param string $tableName
     * @return array
     */
    public function listForeignKeyReferences($tableName)
    {
        $sql = 'SELECT
                    column_name,
                    referenced_table_name,
                    referenced_column_name
                FROM information_schema.key_column_usage
                WHERE
                    table_name = ?
                    AND referenced_table_name IS NOT NULL
                    AND referenced_column_name IS NOT NULL';

        $dbInfo     = $this->fetchAll($sql, array($tableName), ARRAY_A);
        $references = array();

        foreach ($dbInfo as $reference) {
            $column = $reference['column_name'];

            $references[$column] = array(
                'table'  => $reference['referenced_table_name'],
                'column' => $reference['referenced_column_name']
            );
        }

        return $references;
    }

    /**
     * Returns an associative array containing all the unique constraints on a table.
     *
     * The array has the following format:
     *
     * <pre>
     * array(
     *     'key_name' => array(
     *         sequence_in_index => 'column_name'
     *     )
     * )
     * </pre>
     *
     * @param string $tableName
     * @return array
     */
    public function listUniqueConstraints($tableName)
    {
        $uniqueConstraints = array();

        $sql = sprintf(
            'SHOW INDEXES FROM %s WHERE Non_unique = 0 AND Key_name != \'PRIMARY\'',
            $this->adapter->quoteIdentifier($tableName)
        );

        $rows = $this->fetchAll($sql, array($tableName), ARRAY_A);

        foreach ($rows as $row) {
            $uniqueConstraints[$row['Key_name']][$row['Seq_in_index']] = $row['Column_name'];
        }

        return $uniqueConstraints;
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
        $sql    = 'DESCRIBE ' . $this->adapter->quoteIdentifier($tableName, true);
        $result = $this->fetchAll($sql, array(), ARRAY_A);
        $desc   = array();

        $row_defaults = array(
            'Length'          => null,
            'Scale'           => null,
            'Precision'       => null,
            'Unsigned'        => null,
            'Primary'         => false,
            'PrimaryPosition' => null,
            'Identity'        => false
        );
        $i = 1;
        $p = 1;
        foreach ($result as $key => $row) {
            $row = array_merge($row_defaults, $row);
            if (preg_match('/unsigned/', $row['Type'])) {
                $row['Unsigned'] = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                $row['Length'] = $matches[2];
            }
            if (preg_match('/^((?:var)?binary)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                $row['Length'] = $matches[2];
            } elseif (preg_match('/^decimal\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'decimal';
                $row['Precision'] = $matches[1];
                $row['Scale'] = $matches[2];
            } elseif (preg_match('/^float\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'float';
                $row['Precision'] = $matches[1];
                $row['Scale'] = $matches[2];
            } elseif (preg_match('/^double\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'double';
                $row['Precision'] = $matches[1];
                $row['Scale'] = $matches[2];
            } elseif (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                /**
                 * The optional argument of a MySQL int type is not precision
                 * or length; it is only a hint for display width.
                 */
            }
            if (strtoupper($row['Key']) == 'PRI') {
                $row['Primary'] = true;
                $row['PrimaryPosition'] = $p;
                if ($row['Extra'] == 'auto_increment') {
                    $row['Identity'] = true;
                } else {
                    $row['Identity'] = false;
                }
                ++$p;
            }
            $desc[$this->adapter->foldCase($row['Field'])] = array(
                'SCHEMA_NAME'      => null,
                'TABLE_NAME'       => $this->adapter->foldCase($tableName),
                'COLUMN_NAME'      => $this->adapter->foldCase($row['Field']),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row['Type'],
                'DEFAULT'          => $row['Default'],
                'NULLABLE'         => (bool) ($row['Null'] == 'YES'),
                'LENGTH'           => $row['Length'],
                'SCALE'            => $row['Scale'],
                'GENERIC_TYPE'     => $this->mapNativeTypeToGenericType($row['Type'], $row['Length']),
                'PRECISION'        => $row['Precision'],
                'UNSIGNED'         => $row['Unsigned'],
                'PRIMARY'          => $row['Primary'],
                'PRIMARY_POSITION' => $row['PrimaryPosition'],
                'IDENTITY'         => $row['Identity']
            );
            ++$i;
        }
        return $desc;
    }

    /**
     * This method wraps all calls to wpdb.  It is used to catch errors
     * generated by database usage in wpdb and bubble them up as thrown
     * exceptions, which work more consistently in other environments
     * than the quirky error tracking wpdb does internally.
     *
     * @param mixed $wpdbResult
     * @throws \Dewdrop\Exception
     * @return mixed
     */
    private function execWpdb($wpdbResult)
    {
        if ($this->wpdb->last_error) {
            throw new Exception($this->wpdb->last_error);
        }

        return $wpdbResult;
    }

    /**
     * Pick an appropriate generic data type for the supplied MySQL native
     * data type.
     *
     * @param string $nativeType
     * @param mixed $length
     * @return string
     * @throws Exception
     */
    public function mapNativeTypeToGenericType($nativeType, $length)
    {
        switch ($nativeType) {
            case 'tinyint':
                $type = 'integer';

                if (4 >= $length) {
                    $type = 'boolean';
                }
                break;
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'integer':
                $type = 'integer';
                break;
            case 'tinytext':
            case 'mediumtext':
            case 'longtext':
            case 'text':
            case 'varchar':
            case 'string':
            case 'char':
                $type = 'text';
                if ($length == '1') {
                    $type = 'boolean';
                } elseif (strstr($nativeType, 'text')) {
                    $type = 'clob';
                }
                break;
            case 'enum':
                $type   = 'text';
                $length = 0;
                break;
            case 'date':
                $type = 'date';
                break;
            case 'datetime':
            case 'timestamp':
                $type = 'timestamp';
                break;
            case 'time':
                $type = 'time';
                break;
            case 'float':
            case 'double':
            case 'real':
            case 'unknown':
            case 'decimal':
            case 'numeric':
                $type = 'float';
                break;
            case 'tinyblob':
            case 'mediumblob':
            case 'longblob':
            case 'blob':
                $type = 'blob';
                $length = null;
                break;
            case 'binary':
            case 'varbinary':
                $type = 'blob';
                break;
        }

        if (false !== stripos($nativeType, 'enum')) {
            $type = 'text';
        }

        if (!isset($type) || !$type) {
            throw new Exception("Data type {$nativeType} not supported.");
        }

        return $type;
    }

    /**
     * With the legacy mysql extension, which most WP installs still use, this
     * is the only decent way to do transactions.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->query('SET AUTOCOMMIT=0');
        $this->query('START TRANSACTION');
    }

    /**
     * Commit the current transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->query('COMMIT');
        return $this->query('SET AUTOCOMMIT=1');
    }

    /**
     * Rollback the current transaction.
     *
     * @return void
     */
    public function rollback()
    {
        $this->query('ROLLBACK');
    }

    /**
     * Use the SQL_CALC_FOUND_ROWS facility in MySQL to calculate the total
     * number of rows that would have been returned from a query if no LIMIT
     * had been applied.
     *
     * @param Select $select
     * @return void
     */
    public function prepareSelectForTotalRowCalculation(Select $select)
    {
        $select->preColumnsOption('SQL_CALC_FOUND_ROWS');
    }

    /**
     * Use MySQL's FOUND_ROWS() facility to retrieve the total number of rows
     * that would have been fetched on the previous statement if no LIMIT was
     * applied.
     *
     * @param array $resultSet
     * @return integer
     */
    public function fetchTotalRowCount(array $resultSet)
    {
        return $this->adapter->fetchOne('SELECT FOUND_ROWS()');
    }

    /**
     * Return the operator that can be used for case-insensitive LIKE
     * comparisons.
     *
     * @return string
     */
    public function getCaseInsensitiveLikeOperator()
    {
        return 'LIKE';
    }

    /**
     * Use the functions available in the RDBMS to truncate the provided timestamp
     * column to a date.
     *
     * @param string $timestamp
     * @return string
     */
    public function truncateTimeStampToDate($timestamp)
    {
        return "DATE({$timestamp})";
    }

    /**
     * Quote the supplied input using mysql_real_escape_string() because WordPress
     * is really gross.
     *
     * @param string $input
     * @return string
     */
    public function quoteInternal($input)
    {
        return "'" . $this->wpdb->escape($input) . "'";
    }

    /**
     * @return \mysqli
     */
    protected function getMysqli()
    {
        if (!$this->mysqli) {
            $this->mysqli = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        }

        return $this->mysqli;
    }
}
