<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db;

use Dewdrop\Db\Adapter\GroupKeyNotPresentInResultsetException;
use Dewdrop\Db\Driver\DriverInterface;
use Dewdrop\Db\Driver\Wpdb as WpdbDriver;
use Dewdrop\Exception;
use Dewdrop\Paths;
use Dewdrop\Pimple;
use wpdb;

/**
 * This database adapter largely mirrors the Zend_Db API from Zend Framework 1.
 * However, it is altered to accommodate the limitations of wpdb and use the
 * normal wpdb instance created on every WordPress request as its driver.  This
 * allows us to take advantage of the more expressive and powerful DB API
 * from Zend_Db without needing to create a secondary MySQL connection on every
 * request.
 */
class Adapter
{
    const OBJECT = 'OBJECT';

    const OBJECT_K = 'OBJECT_K';

    const ARRAY_A = 'ARRAY_A';

    const ARRAY_N = 'ARRAY_N';

    /**
     * Use the INT_TYPE, BIGINT_TYPE, and FLOAT_TYPE with the quote() method.
     */
    const INT_TYPE    = 0;
    const BIGINT_TYPE = 1;
    const FLOAT_TYPE  = 2;

    /**
     * PDO constant values used by some helper methods on the adapter class.
     */
    const CASE_LOWER = 2;
    const CASE_NATURAL = 0;
    const CASE_UPPER = 1;

    /**
     * How to handle case folding for keys in result sets.
     *
     * @var int
     */
    protected $caseFolding = self::CASE_NATURAL;

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE, or Zend_Db::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $numericDataTypes = array(
        self::INT_TYPE       => self::INT_TYPE,
        self::BIGINT_TYPE    => self::BIGINT_TYPE,
        self::FLOAT_TYPE     => self::FLOAT_TYPE,
        'INT'                => self::INT_TYPE,
        'INTEGER'            => self::INT_TYPE,
        'MEDIUMINT'          => self::INT_TYPE,
        'SMALLINT'           => self::INT_TYPE,
        'TINYINT'            => self::INT_TYPE,
        'BIGINT'             => self::BIGINT_TYPE,
        'SERIAL'             => self::BIGINT_TYPE,
        'DEC'                => self::FLOAT_TYPE,
        'DECIMAL'            => self::FLOAT_TYPE,
        'DOUBLE'             => self::FLOAT_TYPE,
        'DOUBLE PRECISION'   => self::FLOAT_TYPE,
        'FIXED'              => self::FLOAT_TYPE,
        'FLOAT'              => self::FLOAT_TYPE
    );

    /**
     * Whether to quote (i.e. add surrounding backticks to) identifiers like
     * table and column names.
     *
     * @var boolean
     */
    protected $autoQuoteIdentifiers = true;

    /**
     * The driver for this adapter.  Enables handling platform-specific
     * API for wpdb, MySQL, and Postgres.
     *
     * @var \Dewdrop\Db\Driver\DriverInterface
     */
    protected $driver;

    /**
     * The name of the table that was affected by the most recent call to insert()
     * is stored here so that we can use it to retrieve the last insert ID when
     * using PostgreSQL.
     *
     * @var mixed
     */
    protected $lastInsertTableName;

    /**
     * Path to database table metadata.
     *
     * @var string
     */
    protected $tableMetadataPath;

    /**
     * Create new adapter using the wpdb object as the driver
     *
     * @param mixed $driver
     */
    public function __construct($driver = null)
    {
        if ($driver instanceof wpdb) {
            $driver = new WpdbDriver($this, $driver);
        }

        $this->driver = $driver;
    }

    /**
     * Set the driver that will be used by this adapter to communicate directly
     * with the RDBMS.
     *
     * @param DriverInterface $driver
     * @return \Dewdrop\Db\Adapter
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return mixed
     */
    public function getConnection()
    {
        return $this->driver->getConnection();
    }

    /**
     * Get the driver being used to perform work for this DB adapter.
     *
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Returns table metadata information.
     *
     * @param string $table
     * @return array
     * @throws \Dewdrop\Exception
     */
    public function getTableMetadata($table)
    {
        $path = $this->getTableMetadataPath() . "/{$table}.php";

        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception("Could not find metadata for table \"{$table}\"");
        }

        return require $path;
    }

    /**
     * Returns table metadata filesystem path.
     *
     * @return string
     */
    public function getTableMetadataPath()
    {
        if (null === $this->tableMetadataPath) {
            /* @var $paths Paths */
            $paths = Pimple::getResource('paths');
            $this->tableMetadataPath = $paths->getModels() . '/metadata';
        }

        return $this->tableMetadataPath;
    }

    /**
     * Sets table metadata filesystem path.
     *
     * @param string $tableMetadataPath
     * @return Adapter
     */
    public function setTableMetadataPath($tableMetadataPath)
    {
        $this->tableMetadataPath = (string) $tableMetadataPath;

        return $this;
    }

    /**
     * Create a \Dewdrop\Db\Select object.
     *
     * @return \Dewdrop\Db\Select
     */
    public function select()
    {
        return new Select($this);
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
        return $this->driver->fetchAll($sql, $bind, $fetchMode);
    }

    /**
     * Fetch all results for the supplied SQL statement and group them into
     * a nested array using the supplied $groupKey.
     *
     * For exapmle, if you had a resultset containing these three rows:
     *
     * <pre>
     * name | family_id
     * ----------------
     * Bob  | 1
     * Tim  | 1
     * Ken  | 2
     * </pre>
     *
     * And you called fetchAllGroupedByKey() with a $groupKey of 'family_id',
     * you'd get the following array in return:
     *
     * <pre>
     * [
     *     1 => [
     *         ['name' => 'Bob', 'family_id' => 1],
     *         ['name' => 'Tim', 'family_id' => 1],
     *     ],
     *     2 => [
     *         ['name' => 'Ken', 'family_id' => 2],
     *     ]
     * ]
     * </pre>
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param string $groupKey
     * @param array $bind
     * @param string $fetchMode
     * @return array
     */
    public function fetchAllGroupedByKey($sql, $groupKey, $bind = array(), $fetchMode = null)
    {
        $rows = $this->fetchAll($sql, $bind, $fetchMode);
        $out  = [];

        if (count($rows)) {
            $validationRow = current($rows);

            // Convert validation row to array from stdClass in case fetchMode returned an object
            if (is_object($validationRow)) {
                $validationRow = get_object_vars($validationRow);
            }

            if (is_array($validationRow) && !isset($validationRow[$groupKey])) {
                $exception = new GroupKeyNotPresentInResultsetException("'{$groupKey}' was not present in results.");
                $exception
                    ->setGroupKey($groupKey)
                    ->setValidationRow($validationRow);
                throw $exception;
            }
        }

        foreach ($rows as $row) {
            if (is_array($row)) {
                $groupValue = $row[$groupKey];
            } else {
                $groupValue = $row->$groupKey;
            }

            if (!array_key_exists($groupValue, $out)) {
                $out[$groupValue] = [];
            }

            $out[$groupValue][] = $row;
        }

        return $out;
    }

    /**
     * Fetch all results for the supplied SQL statement using a PHP generator.
     *
     * This approach uses less memory, but the result set has a forward-only cursor.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @param string $fetchMode
     * @return \Generator
     */
    public function fetchAllWithGenerator($sql, $bind = [], $fetchMode = null)
    {
        foreach ($this->driver->fetchAllWithGenerator($sql, $bind, $fetchMode) as $row) {
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
        return $this->driver->fetchCol($sql, $bind);
    }

    /**
     * Fetch a single row from the results from the supplied SQL statement.
     *
     * This method still uses a fetchAll internally, so you should either by
     * selecting on a primary key or other unique key or LIMITing your results
     * explicitly.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @param string $fetchMode
     * @return array
     */
    public function fetchRow($sql, $bind = array(), $fetchMode = null)
    {
        $rs = $this->fetchAll($sql, $bind, $fetchMode);

        if ($rs) {
            return current($rs);
        }

        return null;
    }

    /**
     * Returns the first two columns from the SQL results as key-value
     * pairs useful, for examples, for lists of options in a drop-down.
     *
     * @param string|\Dewdrop\Db\Select $sql An SQL SELECT statement.
     * @param array $bind
     * @return array
     */
    public function fetchPairs($sql, $bind = array())
    {
        $rs  = $this->fetchAll($sql, $bind, self::ARRAY_N);
        $out = array();

        foreach ($rs as $row) {
            $out[$row[0]] = $row[1];
        }

        return $out;
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
        return $this->driver->fetchOne($sql, $bind);
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col, true);
            if ($val instanceof Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            } else {
                $vals[] = '?';
            }
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';

        $bind   = array_values($bind);
        $result = $this->query($sql, $bind);

        $this->lastInsertTableName = $table;

        return $result;
    }

    /**
     * Get the last insert ID from after performing an insert on a table
     * with an auto-incrementing primary key.
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->driver->lastInsertId();
    }

    /**
     * Retrieve the name of the table affected by the last call to insert().
     * Allows us to get last insert ID when using drivers that don't have a
     * function for that built-in (i.e. pgsql).
     *
     * @return string
     */
    public function getLastInsertTableName()
    {
        return $this->lastInsertTableName;
    }

    /**
     * Updates table rows with specified data based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  array        $bind  Column-value pairs.
     * @param  mixed        $where UPDATE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function update($table, array $bind, $where = '')
    {
        /**
         * Build "col = ?" pairs for the statement,
         * except for Zend_Db_Expr which is treated literally.
         */
        $set = array();
        $i = 0;
        foreach ($bind as $col => $val) {
            if ($val instanceof Expr) {
                $val = $val->__toString();
                unset($bind[$col]);
            } else {
                $val = '?';
            }
            $set[] = $this->quoteIdentifier($col, true) . ' = ' . $val;
        }

        $where = $this->whereExpr($where);

        /**
         * Build the UPDATE statement
         */
        $sql = "UPDATE "
             . $this->quoteIdentifier($table, true)
             . ' SET ' . implode(', ', $set)
             . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        $result = $this->query($sql, array_values($bind));

        return $result;
    }

    /**
     * Deletes table rows based on a WHERE clause.
     *
     * @param  mixed        $table The table to update.
     * @param  mixed        $where DELETE WHERE clause(s).
     * @return int          The number of affected rows.
     */
    public function delete($table, $where = '')
    {
        $where = $this->whereExpr($where);

        /**
         * Build the DELETE statement
         */
        $sql = "DELETE FROM "
             . $this->quoteIdentifier($table, true)
             . (($where) ? " WHERE $where" : '');

        /**
         * Execute the statement and return the number of affected rows
         */
        return $this->query($sql);
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
        return $this->driver->query($sql, $bind);
    }

    /**
     * Quote the bound values into the provided SQL query.
     *
     * Because the mysql extension used by wpdb does not support prepared
     * statements, we emulate that support here by interpolating the bound
     * values into "?" placeholders in the SQL string.  We have to break
     * the overall SQL command into segments containing a single "?"
     * placeholder each so that we don't mistake "?" characters in previously
     * bound values as new placeholders.
     *
     * @param string|\Dewdrop\Db\Select $sql
     * @param array $bind
     * @return string
     */
    public function prepare($sql, $bind = array())
    {
        $sql   = (string) $sql;
        $start = 0;
        $end   = 0;
        $out   = '';

        foreach ($bind as $position => $param) {
            $end     = strpos(substr($sql, $start), '?') + 1 + $end;
            $segment = substr($sql, $start, $end - $start);

            $out .= $this->quoteInto($segment, $param, null, 1);

            $start = $end;
        }

        // Append any unprocessed portions of the initial SQL string
        $out .= substr($sql, $start);

        return $out;
    }

    /**
     * Quote a database identifier such as a column name or table name
     * to make it safe to include in a SQL statement.
     *
     * @param string $identifier
     * @param boolean $auto
     * @return string
     */
    public function quoteIdentifier($identifier, $auto = false)
    {
        return $this->quoteIdentifierAs($identifier, null, $auto);
    }

    /**
     * Safely quotes a value for an SQL statement.
     *
     * If an array is passed as the value, the array values are quoted
     * and then returned as a comma-separated string.
     *
     * @param mixed $value The value to quote.
     * @param mixed $type  OPTIONAL the SQL datatype name, or constant, or null.
     * @return mixed An SQL-safe quoted value (or string of separated values).
     */
    public function quote($value, $type = null)
    {
        if (null === $value) {
            return 'null';
        }

        if ($value instanceof Select) {
            return '(' . $value->assemble() . ')';
        }

        if ($value instanceof Expr) {
            return $value->__toString();
        }

        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val, $type);
            }
            return implode(', ', $value);
        }

        if ($type !== null && array_key_exists($type = strtoupper($type), $this->numericDataTypes)) {
            $quotedValue = '0';
            switch ($this->numericDataTypes[$type]) {
                case self::INT_TYPE: // 32-bit integer
                    $quotedValue = (string) intval($value);
                    break;
                case self::BIGINT_TYPE: // 64-bit integer
                    // ANSI SQL-style hex literals (e.g. x'[\dA-F]+')
                    // are not supported here, because these are string
                    // literals, not numeric literals.
                    $hasMatch = preg_match(
                        '/^(
                          [+-]?                  # optional sign
                          (?:
                            0[Xx][\da-fA-F]+     # ODBC-style hexadecimal
                            |\d+                 # decimal or octal, or MySQL ZEROFILL decimal
                            (?:[eE][+-]?\d+)?    # optional exponent on decimals or octals
                          )
                        )/x',
                        (string) $value,
                        $matches
                    );

                    if ($hasMatch) {
                        $quotedValue = $matches[1];
                    }
                    break;
                case self::FLOAT_TYPE: // float or decimal
                    $quotedValue = sprintf('%F', $value);
            }
            return $quotedValue;
        }

        return $this->quoteInternal($value);
    }

    /**
     * An internal method used by other quote methods.  This was origianlly
     * _quote() in Zend_Db, but in PSR-2 code style, it had to be renamed to
     * avoid conflicting with the public method of the same name.  Because
     * this adapter only needs to work with MySQL (because that is the only
     * RDBMS supported by WP), this could likely be integrated directly with
     * the quote methods.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function quoteInternal($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return $this->driver->quoteInternal($value);
    }

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example:
     *
     * <pre>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </pre>
     *
     * @param string  $text  The text with a placeholder.
     * @param mixed   $value The value to quote.
     * @param string  $type  OPTIONAL SQL datatype
     * @param integer $count OPTIONAL count of placeholders to replace
     * @return string An SQL-safe quoted value placed into the original text.
     */
    public function quoteInto($text, $value, $type = null, $count = null)
    {
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }

    /**
     * Quote an identifier and an optional alias.
     *
     * @param string|array|Expr $ident The identifier or expression.
     * @param string $alias An optional alias.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @param string $as The string to add between the identifier/expression and the alias.
     * @return string The quoted identifier and alias.
     */
    protected function quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        if ($ident instanceof Expr) {
            $quoted = $ident->__toString();
        } elseif ($ident instanceof Select) {
            $quoted = '(' . $ident->assemble() . ')';
        } else {
            if (is_string($ident)) {
                $ident = explode('.', $ident);
            }
            if (is_array($ident)) {
                $segments = array();
                foreach ($ident as $segment) {
                    if ($segment instanceof Expr) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->quoteIdentifierInternal($segment, $auto);
                    }
                }
                if ($alias !== null && end($ident) == $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->quoteIdentifierInternal($ident, $auto);
            }
        }
        if ($alias !== null) {
            $quoted .= $as . $this->quoteIdentifierInternal($alias, $auto);
        }
        return $quoted;
    }

    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|Expr $ident The identifier or expression.
     * @param string $alias An alias for the table.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null, $auto = false)
    {
        return $this->quoteIdentifierAs($ident, $alias, $auto);
    }

    /**
     * Quote a column identifier and alias.
     *
     * @param string|array|Expr $ident The identifier or expression.
     * @param string $alias An alias for the column.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteColumnAs($ident, $alias, $auto = false)
    {
        return $this->quoteIdentifierAs($ident, $alias, $auto);
    }

    /**
     * Quote an identifier.
     *
     * @param  string $value The identifier or expression.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string        The quoted identifier and alias.
     */
    protected function quoteIdentifierInternal($value, $auto = false)
    {
        if ($auto === false || $this->autoQuoteIdentifiers === true) {
            $q = $this->getQuoteIdentifierSymbol();
            return ($q . str_replace("$q", "$q$q", $value) . $q);
        }
        return $value;
    }

    /**
     * Returns the symbol the adapter uses for delimited identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return $this->driver->getQuoteIdentifierSymbol();
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param int $count
     * @param int $offset OPTIONAL
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            throw new Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        return $this->driver->listTables();
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
        return $this->driver->listForeignKeyReferences($tableName);
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
        return $this->driver->listUniqueConstraints($tableName);
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
        return $this->driver->describeTable($tableName);
    }

    /**
     * Begin a new transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        return $this->driver->beginTransaction();
    }

    /**
     * Commit the current transaction.
     *
     * @return void
     */
    public function commit()
    {
        $this->driver->commit();
    }

    /**
     * Rollback the current transaction.
     *
     * @return void
     */
    public function rollback()
    {
        $this->driver->rollback();
    }

    /**
     * Helper method to change the case of the strings used
     * when returning result sets in FETCH_ASSOC and FETCH_BOTH
     * modes.
     *
     * This is not intended to be used by application code,
     * but the method must be public so the Statement class
     * can invoke it.
     *
     * @param string $key
     * @return string
     */
    public function foldCase($key)
    {
        switch ($this->caseFolding) {
            case self::CASE_LOWER:
                $value = strtolower((string) $key);
                break;
            case self::CASE_UPPER:
                $value = strtoupper((string) $key);
                break;
            case self::CASE_NATURAL:
            default:
                $value = (string) $key;
        }
        return $value;
    }

    /**
     * Convert an array, string, or Zend_Db_Expr object
     * into a string to put in a WHERE clause.
     *
     * @param mixed $where
     * @return string
     */
    protected function whereExpr($where)
    {
        if (empty($where)) {
            return $where;
        }
        if (!is_array($where)) {
            $where = array($where);
        }
        foreach ($where as $cond => &$term) {
            // is $cond an int? (i.e. Not a condition)
            if (is_int($cond)) {
                // $term is the full condition
                if ($term instanceof Expr) {
                    $term = $term->__toString();
                }
            } else {
                // $cond is the condition with placeholder,
                // and $term is quoted into the condition
                $term = $this->quoteInto($cond, $term);
            }
            $term = '(' . $term . ')';
        }

        $where = implode(' AND ', $where);
        return $where;
    }
}
