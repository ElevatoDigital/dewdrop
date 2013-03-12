<?php

namespace Dewdrop\Db;

use Dewdrop\Db\Statement\StatementInterface;
use Dewdrop\Db\Adapter;
use Dewdrop\Db\Statement\StatementException;

class Statement implements StatementInterface
{
    /**
     * @var resource|object The driver level statement object/resource
     */
    protected $stmt = null;

    /**
     * @var \Dewdrop\Db\Adapter
     */
    protected $adapter = null;

    /**
     * The current fetch mode.
     *
     * @var integer
     */
    protected $fetchMode = \Dewdrop\Db\Adapter::FETCH_ASSOC;

    /**
     * Attributes.
     *
     * @var array
     */
    protected $attribute = array();

    /**
     * Column result bindings.
     *
     * @var array
     */
    protected $bindColumn = array();

    /**
     * Query parameter bindings; covers bindParam() and bindValue().
     *
     * @var array
     */
    protected $bindParam = array();

    /**
     * SQL string split into an array at placeholders.
     *
     * @var array
     */
    protected $sqlSplit = array();

    /**
     * Parameter placeholders in the SQL string by position in the split array.
     *
     * @var array
     */
    protected $sqlParam = array();

    /**
     * @var Zend_Db_Profiler_Query
     */
    protected $queryId = null;

    /**
     * Column names.
     *
     * @var array
     */
    protected $keys;

    /**
     * Fetched result values.
     *
     * @var array
     */
    protected $values;

    /**
     * @var array
     */
    protected $meta = null;

    /**
     * Constructor for a statement.
     *
     * @param \Dewdrop\Db\Adapter $adapter
     * @param mixed $sql Either a string or Zend_Db_Select.
     */
    public function __construct($adapter, $sql)
    {
        $this->adapter = $adapter;
        if ($sql instanceof \Dewdrop\Db\Select) {
            $sql = $sql->assemble();
        }
        $this->parseParameters($sql);
        $this->prepare($sql);

        $this->queryId = $this->adapter->getProfiler()->queryStart($sql);
    }

    /**
     * Internal method called by abstract statment constructor to setup
     * the driver level statement
     *
     * @return void
     */
    protected function _prepare($sql)
    {
        return;
    }

    /**
     * @param string $sql
     * @return void
     */
    protected function parseParameters($sql)
    {
        $sql = $this->stripQuoted($sql);

        // split into text and params
        $this->sqlSplit = preg_split('/(\?|\:[a-zA-Z0-9_]+)/',
            $sql, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        // map params
        $this->sqlParam = array();
        foreach ($this->sqlSplit as $key => $val) {
            if ($val == '?') {
                if ($this->adapter->supportsParameters('positional') === false) {
                    throw new StatementException("Invalid bind-variable position '$val'");
                }
            } else if ($val[0] == ':') {
                if ($this->adapter->supportsParameters('named') === false) {
                    throw new StatementException("Invalid bind-variable name '$val'");
                }
            }
            $this->sqlParam[] = $val;
        }

        // set up for binding
        $this->bindParam = array();
    }

    /**
     * Remove parts of a SQL string that contain quoted strings
     * of values or identifiers.
     *
     * @param string $sql
     * @return string
     */
    protected function stripQuoted($sql)
    {
        // get the character for delimited id quotes,
        // this is usually " but in MySQL is `
        $d = $this->adapter->quoteIdentifier('a');
        $d = $d[0];

        // get the value used as an escaped delimited id quote,
        // e.g. \" or "" or \`
        $de = $this->adapter->quoteIdentifier($d);
        $de = substr($de, 1, 2);
        $de = str_replace('\\', '\\\\', $de);

        // get the character for value quoting
        // this should be '
        $q = $this->adapter->quote('a');
        $q = $q[0];

        // get the value used as an escaped quote,
        // e.g. \' or ''
        $qe = $this->adapter->quote($q);
        $qe = substr($qe, 1, 2);
        $qe = str_replace('\\', '\\\\', $qe);

        // get a version of the SQL statement with all quoted
        // values and delimited identifiers stripped out
        // remove "foo\"bar"
        $sql = preg_replace("/$q($qe|\\\\{2}|[^$q])*$q/", '', $sql);
        // remove 'foo\'bar'
        if (!empty($q)) {
            $sql = preg_replace("/$q($qe|[^$q])*$q/", '', $sql);
        }

        return $sql;
    }

    /**
     * Bind a column of the statement result set to a PHP variable.
     *
     * @param string $column Name the column in the result set, either by
     *                       position or by name.
     * @param mixed  $param  Reference to the PHP variable containing the value.
     * @param mixed  $type   OPTIONAL
     * @return bool
     */
    public function bindColumn($column, &$param, $type = null)
    {
        $this->bindColumn[$column] =& $param;
        return true;
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL Datatype of SQL parameter.
     * @param mixed $length    OPTIONAL Length of SQL parameter.
     * @param mixed $options   OPTIONAL Other options.
     * @return bool
     */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        if (!is_int($parameter) && !is_string($parameter)) {
            throw new StatementException('Invalid bind-variable position');
        }

        $position = null;
        if (($intval = (int) $parameter) > 0 && $this->adapter->supportsParameters('positional')) {
            if ($intval >= 1 || $intval <= count($this->sqlParam)) {
                $position = $intval;
            }
        } else if ($this->adapter->supportsParameters('named')) {
            if ($parameter[0] != ':') {
                $parameter = ':' . $parameter;
            }
            if (in_array($parameter, $this->sqlParam) !== false) {
                $position = $parameter;
            }
        }

        if ($position === null) {
            throw new StatementException("Invalid bind-variable position '$parameter'");
        }

        // Finally we are assured that $position is valid
        $this->bindParam[$position] =& $variable;
        return $this->bindParam($position, $variable, $type, $length, $options);
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $value     Scalar value to bind to the parameter.
     * @param mixed $type      OPTIONAL Datatype of the parameter.
     * @return bool
     */
    public function bindValue($parameter, $value, $type = null)
    {
        return $this->bindParam($parameter, $value, $type);
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * @return array Collection of rows, each in a format by the fetch mode.
     */
    public function fetchAll($style = null, $col = null)
    {
        $data = array();
        if ($style === Adapter::FETCH_COLUMN && $col === null) {
            $col = 0;
        }
        if ($col === null) {
            while ($row = $this->fetch($style)) {
                $data[] = $row;
            }
        } else {
            while (false !== ($val = $this->fetchColumn($col))) {
                $data[] = $val;
            }
        }
        return $data;
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $col OPTIONAL Position of the column to fetch.
     * @return string One value from the next row of result set, or false.
     */
    public function fetchColumn($col = 0)
    {
        $data = array();
        $col = (int) $col;
        $row = $this->fetch(Adapter::FETCH_NUM);
        if (!is_array($row)) {
            return false;
        }
        return $row[$col];
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * @return mixed One object instance of the specified class, or false.
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        $obj = new $class($config);
        $row = $this->fetch(Adapter::FETCH_ASSOC);
        if (!is_array($row)) {
            return false;
        }
        foreach ($row as $key => $val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    /**
     * Retrieve a statement attribute.
     *
     * @param string $key Attribute name.
     * @return mixed      Attribute value.
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attribute)) {
            return $this->attribute[$key];
        }
    }

    /**
     * Set a statement attribute.
     *
     * @param string $key Attribute name.
     * @param mixed  $val Attribute value.
     * @return bool
     */
    public function setAttribute($key, $val)
    {
        $this->attribute[$key] = $val;
    }

    /**
     * Set the default fetch mode for this statement.
     *
     * @param int   $mode The fetch mode.
     * @return bool
     * @throws \Dewdrop\Db\Statement\Exception
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case Adapter::FETCH_NUM:
            case Adapter::FETCH_ASSOC:
            case Adapter::FETCH_BOTH:
            case Adapter::FETCH_OBJ:
                $this->fetchMode = $mode;
                break;
            case Adapter::FETCH_BOUND:
            default:
                $this->closeCursor();
                /**
                 * @see Zend_Db_Statement_Exception
                 */
                throw new StatementException('invalid fetch mode');
                break;
        }
    }

    /**
     * Helper function to map retrieved row
     * to bound column variables
     *
     * @param array $row
     * @return bool True
     */
    public function fetchBound($row)
    {
        foreach ($row as $key => $value) {
            // bindColumn() takes 1-based integer positions
            // but fetch() returns 0-based integer indexes
            if (is_int($key)) {
                $key++;
            }
            // set results only to variables that were bound previously
            if (isset($this->bindColumn[$key])) {
                $this->bindColumn[$key] = $value;
            }
        }
        return true;
    }

    /**
     * Gets the \Dewdrop\Db\Adapter for this
     * particular \Dewdrop\Db\Statement object.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Gets the resource or object setup by the
     * _parse
     * @return unknown_type
     */
    public function getDriverStatement()
    {
        return $this->stmt;
    }

    /**
     * @param  string $sql
     * @return void
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function prepare($sql)
    {
        $wpdb = $this->adapter->getConnection();

        $this->stmt = $wpdb->prepare($sql);

        if ($this->stmt === false) {
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli prepare error: " . $mysqli->error, $mysqli->errno);
        }
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL Datatype of SQL parameter.
     * @param mixed $length    OPTIONAL Length of SQL parameter.
     * @param mixed $options   OPTIONAL Other options.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    protected function _bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        return true;
    }

    /**
     * Closes the cursor and the statement.
     *
     * @return bool
     */
    public function close()
    {
        if ($this->stmt) {
            $r = $this->stmt->close();
            $this->stmt = null;
            return $r;
        }
        return false;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor()
    {
        return false;
    }

    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     */
    public function columnCount()
    {
        if (isset($this->meta) && $this->meta) {
            return $this->meta->field_count;
        }
        return 0;
    }

    /**
     * Retrieves the error code, if any, associated with the last operation on
     * the statement handle.
     *
     * @return string error code.
     */
    public function errorCode()
    {
        if (!$this->stmt) {
            return false;
        }
        return substr($this->stmt->sqlstate, 0, 5);
    }

    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        if (!$this->stmt) {
            return false;
        }
        return array(
            substr($this->stmt->sqlstate, 0, 5),
            $this->stmt->errno,
            $this->stmt->error,
        );
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function execute(array $params = null)
    {
        if (!$this->stmt) {
            return false;
        }

        // if no params were given as an argument to execute(),
        // then default to the _bindParam array
        if ($params === null) {
            $params = $this->bindParam;
        }
        // send $params as input parameters to the statement
        if ($params) {
            array_unshift($params, str_repeat('s', count($params)));
            $stmtParams = array();
            foreach ($params as $k => &$value) {
                $stmtParams[$k] = &$value;
            }
            call_user_func_array(
                array($this->stmt, 'bind_param'),
                $stmtParams
                );
        }

        // execute the statement
        $retval = $this->stmt->execute();
        if ($retval === false) {
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement execute error : " . $this->_stmt->error, $this->_stmt->errno);
        }


        // retain metadata
        if ($this->meta === null) {
            $this->meta = $this->stmt->result_metadata();
            if ($this->stmt->errno) {
                throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement metadata error: " . $this->_stmt->error, $this->_stmt->errno);
            }
        }

        // statements that have no result set do not return metadata
        if ($this->meta !== false) {

            // get the column names that will result
            $this->keys = array();
            foreach ($this->meta->fetch_fields() as $col) {
                $this->keys[] = $this->adapter->foldCase($col->name);
            }

            // set up a binding space for result variables
            $this->values = array_fill(0, count($this->keys), null);

            // set up references to the result binding space.
            // just passing $this->_values in the call_user_func_array()
            // below won't work, you need references.
            $refs = array();
            foreach ($this->values as $i => &$f) {
                $refs[$i] = &$f;
            }

            $this->stmt->store_result();
            // bind to the result variables
            call_user_func_array(
                array($this->stmt, 'bind_result'),
                $this->values
            );
        }
        return $retval;
    }


    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->stmt) {
            return false;
        }
        // fetch the next result
        $retval = $this->stmt->fetch();
        switch ($retval) {
            case null: // end of data
            case false: // error occurred
                $this->stmt->reset();
                return false;
            default:
                // fallthrough
        }

        // make sure we have a fetch mode
        if ($style === null) {
            $style = $this->fetchMode;
        }

        // dereference the result values, otherwise things like fetchAll()
        // return the same values for every entry (because of the reference).
        $values = array();
        foreach ($this->values as $key => $val) {
            $values[] = $val;
        }

        $row = false;
        switch ($style) {
            case Adapter::FETCH_NUM:
                $row = $values;
                break;
            case Adapter::FETCH_ASSOC:
                $row = array_combine($this->keys, $values);
                break;
            case Adapter::FETCH_BOTH:
                $assoc = array_combine($this->keys, $values);
                $row = array_merge($values, $assoc);
                break;
            case Adapter::FETCH_OBJ:
                $row = (object) array_combine($this->keys, $values);
                break;
            case Adapter::FETCH_BOUND:
                $assoc = array_combine($this->keys, $values);
                $row = array_merge($values, $assoc);
                return $this->fetchBound($row);
                break;
            default:
                throw new Zend_Db_Statement_Mysqli_Exception("Invalid fetch mode '$style' specified");
                break;
        }
        return $row;
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function nextRowset()
    {
        throw new Zend_Db_Statement_Mysqli_Exception(__FUNCTION__.'() is not implemented');
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     */
    public function rowCount()
    {
        if (!$this->_adapter) {
            return false;
        }
        $mysqli = $this->_adapter->getConnection();
        return $mysqli->affected_rows;
    }

}
