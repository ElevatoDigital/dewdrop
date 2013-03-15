<?php

namespace Dewdrop\Db;

use Dewdrop\Exception;

class Adapter
{
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

    protected $autoQuoteIdentifiers = true;

    /**
     * @var
     */
    protected $fetchMode = ARRAY_A;

    /**
     * @var \wpdb
     */
    private $wpdb;

    /**
     * @param \wpdb $wpdb
     */
    public function __construct(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * Returns the underlying database connection object or resource.
     * If not presently connected, this initiates the connection.
     *
     * @return wpdb
     */
    public function getConnection()
    {
        return $this->wpdb;
    }

    public function select()
    {
        return new Select($this);
    }

    public function fetchAll($sql, $fetchMode = null)
    {
        if (null === $fetchMode) {
            $fetchMode = $this->fetchMode;
        }

        $rs = $this->wpdb->get_results((string) $sql, $fetchMode);

        return $rs;
    }

    public function fetchCol($sql)
    {
        return $this->wpdb->get_col($sql);
    }

    public function fetchRow($sql, $fetchMode = null)
    {
        $rs = $this->fetchAll($sql, $fetchMode);

        if ($rs) {
            return current($rs);
        }

        return null;
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * @param string|Select $sql An SQL SELECT statement.
     * @return array
     */
    public function fetchPairs($sql)
    {
        $rs  = $this->fetchAll($sql, ARRAY_N);
        $out = array();

        foreach ($rs as $row) {
            $out[$row[0]] = $row[1];
        }

        return $out;
    }

    public function fetchOne($sql)
    {
        return $this->wpdb->get_var($sql);
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
                if ($this->supportsParameters('positional')) {
                    $vals[] = '?';
                } else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col'.$i] = $val;
                        $vals[] = ':col'.$i;
                        $i++;
                    } else {
                        throw new Exception(get_class($this) ." doesn't support positional or named binding");
                    }
                }
            }
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';

        // execute the statement and return the number of affected rows
        if ($this->supportsParameters('positional')) {
            $bind = array_values($bind);
        }

        return $this->query($sql, $bind);
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
                if ($this->supportsParameters('positional')) {
                    $val = '?';
                } else {
                    if ($this->supportsParameters('named')) {
                        unset($bind[$col]);
                        $bind[':col'.$i] = $val;
                        $val = ':col'.$i;
                        $i++;
                    } else {
                        /** @see Zend_Db_Adapter_Exception */
                        // require_once 'Zend/Db/Adapter/Exception.php';
                        throw new Zend_Db_Adapter_Exception(get_class($this) ." doesn't support positional or named binding");
                    }
                }
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
        if ($this->supportsParameters('positional')) {
            $result = $this->query($sql, array_values($bind));
        } else {
            $result = $this->query($sql, $bind);
        }

        return $result;
    }

    public function query($sql, $bind = array())
    {
        foreach ($bind as $position => $param) {
            $sql = $this->quoteInto($sql, $param, null, 1);
        }

        return $this->wpdb->query($sql);
    }

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

    protected function quoteInternal($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        return "'" . mysql_real_escape_string($value) . "'";
    }

    /**
     * Quotes a value and places into a piece of text at a placeholder.
     *
     * The placeholder is a question-mark; all placeholders will be replaced
     * with the quoted value.   For example:
     *
     * <code>
     * $text = "WHERE date < ?";
     * $date = "2005-01-02";
     * $safe = $sql->quoteInto($text, $date);
     * // $safe = "WHERE date < '2005-01-02'"
     * </code>
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
        return "`";
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
        return $this->fetchCol('SHOW TABLES');
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
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        if ($schemaName) {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName", true);
        } else {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier($tableName, true);
        }

        $result = $this->fetchAll($sql, ARRAY_A);
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
            } elseif (preg_match('/^decimal\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'decimal';
                $row['Precision'] = $matches[1];
                $row['Scale'] = $matches[2];
            } elseif (preg_match('/^float\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'float';
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
            $desc[$this->foldCase($row['Field'])] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $this->foldCase($row['Field']),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row['Type'],
                'DEFAULT'          => $row['Default'],
                'NULLABLE'         => (bool) ($row['Null'] == 'YES'),
                'LENGTH'           => $row['Length'],
                'SCALE'            => $row['Scale'],
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
        switch ($this->_caseFolding) {
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

    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     * @return bool
     */
    public function supportsParameters($type)
    {
        switch ($type) {
            case 'positional':
                return true;
            case 'named':
            default:
                return false;
        }
    }
}
