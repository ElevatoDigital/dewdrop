<?php

namespace Dewdrop\Db;

class Adapter
{
    /**
     * Use the INT_TYPE, BIGINT_TYPE, and FLOAT_TYPE with the quote() method.
     */
    const INT_TYPE    = 0;
    const BIGINT_TYPE = 1;
    const FLOAT_TYPE  = 2;

    /**
     * PDO constant values discovered by this script result:
     *
     * $list = array(
     *    'PARAM_BOOL', 'PARAM_NULL', 'PARAM_INT', 'PARAM_STR', 'PARAM_LOB',
     *    'PARAM_STMT', 'PARAM_INPUT_OUTPUT', 'FETCH_LAZY', 'FETCH_ASSOC',
     *    'FETCH_NUM', 'FETCH_BOTH', 'FETCH_OBJ', 'FETCH_BOUND',
     *    'FETCH_COLUMN', 'FETCH_CLASS', 'FETCH_INTO', 'FETCH_FUNC',
     *    'FETCH_GROUP', 'FETCH_UNIQUE', 'FETCH_CLASSTYPE', 'FETCH_SERIALIZE',
     *    'FETCH_NAMED', 'ATTR_AUTOCOMMIT', 'ATTR_PREFETCH', 'ATTR_TIMEOUT',
     *    'ATTR_ERRMODE', 'ATTR_SERVER_VERSION', 'ATTR_CLIENT_VERSION',
     *    'ATTR_SERVER_INFO', 'ATTR_CONNECTION_STATUS', 'ATTR_CASE',
     *    'ATTR_CURSOR_NAME', 'ATTR_CURSOR', 'ATTR_ORACLE_NULLS',
     *    'ATTR_PERSISTENT', 'ATTR_STATEMENT_CLASS', 'ATTR_FETCH_TABLE_NAMES',
     *    'ATTR_FETCH_CATALOG_NAMES', 'ATTR_DRIVER_NAME',
     *    'ATTR_STRINGIFY_FETCHES', 'ATTR_MAX_COLUMN_LEN', 'ERRMODE_SILENT',
     *    'ERRMODE_WARNING', 'ERRMODE_EXCEPTION', 'CASE_NATURAL',
     *    'CASE_LOWER', 'CASE_UPPER', 'NULL_NATURAL', 'NULL_EMPTY_STRING',
     *    'NULL_TO_STRING', 'ERR_NONE', 'FETCH_ORI_NEXT',
     *    'FETCH_ORI_PRIOR', 'FETCH_ORI_FIRST', 'FETCH_ORI_LAST',
     *    'FETCH_ORI_ABS', 'FETCH_ORI_REL', 'CURSOR_FWDONLY', 'CURSOR_SCROLL',
     *    'ERR_CANT_MAP', 'ERR_SYNTAX', 'ERR_CONSTRAINT', 'ERR_NOT_FOUND',
     *    'ERR_ALREADY_EXISTS', 'ERR_NOT_IMPLEMENTED', 'ERR_MISMATCH',
     *    'ERR_TRUNCATED', 'ERR_DISCONNECTED', 'ERR_NO_PERM',
     * );
     *
     * $const = array();
     * foreach ($list as $name) {
     *    $const[$name] = constant("PDO::$name");
     * }
     * var_export($const);
     */
    const ATTR_AUTOCOMMIT = 0;
    const ATTR_CASE = 8;
    const ATTR_CLIENT_VERSION = 5;
    const ATTR_CONNECTION_STATUS = 7;
    const ATTR_CURSOR = 10;
    const ATTR_CURSOR_NAME = 9;
    const ATTR_DRIVER_NAME = 16;
    const ATTR_ERRMODE = 3;
    const ATTR_FETCH_CATALOG_NAMES = 15;
    const ATTR_FETCH_TABLE_NAMES = 14;
    const ATTR_MAX_COLUMN_LEN = 18;
    const ATTR_ORACLE_NULLS = 11;
    const ATTR_PERSISTENT = 12;
    const ATTR_PREFETCH = 1;
    const ATTR_SERVER_INFO = 6;
    const ATTR_SERVER_VERSION = 4;
    const ATTR_STATEMENT_CLASS = 13;
    const ATTR_STRINGIFY_FETCHES = 17;
    const ATTR_TIMEOUT = 2;
    const CASE_LOWER = 2;
    const CASE_NATURAL = 0;
    const CASE_UPPER = 1;
    const CURSOR_FWDONLY = 0;
    const CURSOR_SCROLL = 1;
    const ERR_ALREADY_EXISTS = null;
    const ERR_CANT_MAP = null;
    const ERR_CONSTRAINT = null;
    const ERR_DISCONNECTED = null;
    const ERR_MISMATCH = null;
    const ERR_NO_PERM = null;
    const ERR_NONE = '00000';
    const ERR_NOT_FOUND = null;
    const ERR_NOT_IMPLEMENTED = null;
    const ERR_SYNTAX = null;
    const ERR_TRUNCATED = null;
    const ERRMODE_EXCEPTION = 2;
    const ERRMODE_SILENT = 0;
    const ERRMODE_WARNING = 1;
    const FETCH_ASSOC = 2;
    const FETCH_BOTH = 4;
    const FETCH_BOUND = 6;
    const FETCH_CLASS = 8;
    const FETCH_CLASSTYPE = 262144;
    const FETCH_COLUMN = 7;
    const FETCH_FUNC = 10;
    const FETCH_GROUP = 65536;
    const FETCH_INTO = 9;
    const FETCH_LAZY = 1;
    const FETCH_NAMED = 11;
    const FETCH_NUM = 3;
    const FETCH_OBJ = 5;
    const FETCH_ORI_ABS = 4;
    const FETCH_ORI_FIRST = 2;
    const FETCH_ORI_LAST = 3;
    const FETCH_ORI_NEXT = 0;
    const FETCH_ORI_PRIOR = 1;
    const FETCH_ORI_REL = 5;
    const FETCH_SERIALIZE = 524288;
    const FETCH_UNIQUE = 196608;
    const NULL_EMPTY_STRING = 1;
    const NULL_NATURAL = 0;
    const NULL_TO_STRING = null;
    const PARAM_BOOL = 5;
    const PARAM_INPUT_OUTPUT = -2147483648;
    const PARAM_INT = 1;
    const PARAM_LOB = 3;
    const PARAM_NULL = 0;
    const PARAM_STMT = 4;
    const PARAM_STR = 2;

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

    protected $fetchMode = self::FETCH_ASSOC;

    protected $defaultStmtClass = '\Dewdrop\Db\Statement';

    protected $stmt;

    private $wpdb;

    public function __construct(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function getConnection()
    {
        return $this->wpdb;
    }

    public function select()
    {
        return new Select($this);
    }

    public function fetchAll($sql)
    {
        return $this->wpdb->get_results((string) $sql);
    }

    /**
     * Fetches the first column of all SQL result rows as an array.
     *
     * @param string|Zend_Db_Select $sql An SQL SELECT statement.
     * @param mixed $bind Data to bind into SELECT placeholders.
     * @return array
     */
    public function fetchCol($sql, $bind = array())
    {
        $stmt = $this->query($sql, $bind);
        $result = $stmt->fetchAll(self::FETCH_COLUMN, 0);
        return $result;
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

    /**
     * Prepares and executes an SQL statement with bound data.
     *
     * @param  mixed  $sql  The SQL statement with placeholders.
     *                      May be a string or Zend_Db_Select.
     * @param  mixed  $bind An array of data to bind to the placeholders.
     * @return \Dewdrop\Db\Statement\StatementInterface
     */
    public function query($sql, $bind = array())
    {
        // is the $sql a \Dewdrop\Db\Select object?
        if ($sql instanceof \Dewdrop\Db\Select) {
            if (empty($bind)) {
                $bind = $sql->getBind();
            }

            $sql = $sql->assemble();
        }

        // make sure $bind to an array;
        // don't use (array) typecasting because
        // because $bind may be a Zend_Db_Expr object
        if (!is_array($bind)) {
            $bind = array($bind);
        }

        // prepare and execute the statement with profiling
        $stmt = $this->prepare($sql);
        $stmt->execute($bind);

        // return the results embedded in the prepared statement object
        $stmt->setFetchMode($this->fetchMode);
        return $stmt;
    }

    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param  string  $sql  SQL query
     * @return \Dewdrop\Db\Statement
     */
    public function prepare($sql)
    {
        if ($this->stmt) {
            $this->stmt->close();
        }
        $stmtClass = $this->defaultStmtClass;
        $stmt      = new $stmtClass($this, $sql);
        if ($stmt === false) {
            return false;
        }
        $stmt->setFetchMode($this->fetchMode);
        $this->stmt = $stmt;
        return $stmt;
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
            throw new Zend_Db_Adapter_Mysqli_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Zend_Db_Adapter_Mysqli_Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }
}
