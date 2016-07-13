<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\Expr;
use Dewdrop\Db\Select;
use Dewdrop\Exception as DewdropException;
use Dewdrop\Fields\OptionPairs\TitleColumnNotDetectedException;

/**
 * The OptionPairs class makes it easy to retrieve a list of key-value pairs
 * for use as options for a field's value, typically a foreign key.
 */
class OptionPairs
{
    /**
     * The database adapter that will be used to retrieve the options.
     *
     * @var \Dewdrop\Db\Adapter
     */
    protected $dbAdapter;

    /**
     * The name of the table from which the options will be retrieved.
     *
     * @var string
     */
    protected $tableName;

    /**
     * The name of the column that should be used for the value.
     *
     * @var string
     */
    protected $valueColumn;

    /**
     * The name of the column that should be used for the option's title.
     * A \Dewdrop\Db\Expr can also be used for the title column, if you'd
     * like to concatenate multiple columns or format them.
     *
     * @var Expr|string
     */
    protected $titleColumn;

    /**
     * The \Dewdrop\Db\Select that will be used to retrieve the options.
     *
     * @var \Dewdrop\Db\Select
     */
    protected $stmt;

    /**
     * Create new OptionPairs object using supplied DB adapter.
     *
     * @param Adapter $dbAdapter
     */
    public function __construct(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get the title column.
     *
     * @return Expr|string
     */
    public function getTitleColumn()
    {
        return $this->titleColumn;
    }

    public function detectTitleColumn()
    {
        if ($this->titleColumn) {
            return $this->titleColumn;
        } else {
            $metadata = $this->loadTableMetadata();
            return $this->findTitleColumnFromMetadata($metadata['columns']);
        }
    }

    /**
     * Set one more options on this object.  If a setter is not available
     * for the option designated by keys in your options array, an
     * exception will be thrown.
     *
     * @throws \Dewdrop\Exception
     * @param array $options
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                throw new DewdropException("OptionPairs: Unknown option \"{$key}\"");
            }
        }

        return $this;
    }

    /**
     * Set the name of the table where options can be found.
     *
     * @param string $tableName
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Set the name of the column that should be used for the value of each
     * option.
     *
     * @param string $valueColumn
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function setValueColumn($valueColumn)
    {
        $this->valueColumn = $valueColumn;

        return $this;
    }

    /**
     * Set the name of the column that should be used for the title of each
     * option.  You can also use a \Dewdrop\Db\Expr here, if you'd like to
     * do any special formatting or concatenate multiple columns.
     *
     * @param Expr|string $titleColumn
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function setTitleColumn($titleColumn)
    {
        $this->titleColumn = $titleColumn;

        return $this;
    }

    /**
     * An alias for getStmt().
     *
     * @return Select
     */
    public function getSelect()
    {
        return $this->getStmt();
    }

    /**
     * An alias for setStmt().
     *
     * @param Select $select
     * @return OptionPairs
     */
    public function setSelect(Select $select)
    {
        return $this->setStmt($select);
    }

    /**
     * Get the \Dewdrop\Db\Select that will be used for retrieving options.  If
     * one isn't already available, it will be generated using the other
     * properties on this object.  You can call this method to get a good
     * starting point for your SQL and manipulate it from there to apply any
     * specials filters or sorts.
     *
     * @return \Dewdrop\Db\Select
     */
    public function getStmt()
    {
        if (!$this->stmt) {
            $this->stmt = $this->generateStmt();
        }

        return $this->stmt;
    }

    /**
     * Manually set the \Dewdrop\Db\Select that will be used to retrieve
     * options.  This can be useful if you'd prefer to skip the default SQL
     * generation logic completely.
     *
     * @param Select $stmt
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function setStmt(Select $stmt)
    {
        $this->stmt = $stmt;

        return $this;
    }

    /**
     * Fetch the option pairs using the DB adapter.
     *
     * @return array
     */
    public function fetch()
    {
        return $this->dbAdapter->fetchPairs($this->getStmt());
    }

    /**
     * Fetch the option pairs and wrap them in an array that will preserve their
     * sort order when passed by to the client in JSON.  If you just use a normal
     * key-value array here, like you'd get from fetch(), the options will be
     * sorted by their numeric key value in JSON/JavaScript rather than by the
     * sort order defined in the option Select statement.
     *
     * Returns an array where each element is another associated array containing
     * "value" and "title" keys.
     *
     * @return array
     */
    public function fetchJsonWrapper()
    {
        return $this->formatJsonWrapper($this->fetch());
    }

    protected function formatJsonWrapper(array $options)
    {
        $output  = [];

        foreach ($options as $value => $title) {
            $output[] = [
                'value' => $value,
                'title' => $title
            ];
        }

        return $output;
    }

    /**
     * Generate a \Dewdrop\Db\Select object for retrieving options using the
     * metadata of the option table.  Prior to this method being called, at
     * least the $tableName property must be set.  We will attempt to guess
     * the value and title columns, if they are not set, but the table is needed
     * to retrieve the other metadata.
     *
     * @return \Dewdrop\Db\Select
     */
    protected function generateStmt()
    {
        $stmt     = $this->dbAdapter->select();
        $metadata = $this->loadTableMetadata();
        $columns  = $metadata['columns'];

        if (!$this->titleColumn) {
            $this->titleColumn = $this->findTitleColumnFromMetadata($columns);
        }

        if (!$this->valueColumn) {
            $this->valueColumn = $this->findValueColumnFromMetadata($columns);
        }

        $stmt->from($this->tableName, $this->getSelectColumns());

        $this->filterStmt($columns, $stmt);
        $this->orderStmt($columns, $stmt);

        return $stmt;
    }

    protected function getSelectColumns()
    {
        return [
            'value' => $this->valueColumn,
            'title' => $this->titleColumn
        ];
    }

    /**
     * Check to see if a title column has been set.
     *
     * @return boolean
     */
    protected function hasTitleColumn()
    {
        return null !== $this->titleColumn;
    }

    /**
     * This method is called when no title column has been set prior to the
     * generateStmt() method being called.  If the options table has a "name"
     * or "title" column, those will be used.  If not, we'll use the first
     * CHAR or VARCHAR column found in the table metadata.  If we still have
     * not found a suitable candidate, an exception will be thrown telling
     * the developer to manually specify the title column.
     *
     * @throws TitleColumnNotDetectedException
     * @param array $columns The "columns" portion of the table metadata.
     * @return string
     */
    protected function findTitleColumnFromMetadata(array $columns)
    {
        if (array_key_exists('name', $columns)) {
            return 'name';
        }

        if (array_key_exists('title', $columns)) {
            return 'title';
        }

        foreach ($columns as $column => $meta) {
            if ('text' === $meta['GENERIC_TYPE']) {
                return $column;
            }
        }

        $exception = new TitleColumnNotDetectedException('Title column could not be auto-detected.');
        $exception
            ->setTableName($this->tableName)
            ->setColumns($columns);
        throw $exception;
    }

    /**
     * This method is called if now value column has been set by the time
     * generateStmt() is called.  We'll attempt to use the first column
     * from the options table that is part of the primary key.  If no
     * primary key column is found (what have you done!?), we'll throw
     * an exception asking the developer to manually specify the value
     * column.
     *
     * @throws \Dewdrop\Exception
     * @param array $columns
     * @return string
     */
    protected function findValueColumnFromMetadata(array $columns)
    {
        foreach ($columns as $column => $meta) {
            if ($meta['PRIMARY']) {
                return $column;
            }
        }

        throw new DewdropException('OptionPairs: Could not auto-detect value column.  Please specify manually.');
    }

    /**
     * Attempt to apply some default filters to a generated options
     * statement.  Dewdrop supported two similar conventions here: "active"
     * columns and "deleted" columns.  If your options table has an "active"
     * column, only options for which "active" is true will be included.
     * If your options table has a "deleted" column, options with that
     * column set as true will be excluded.
     *
     * @param array $columns The columns portion of the table metadata.
     * @param Select $stmt
     * @return \Dewdrop\Db\Select
     */
    protected function filterStmt($columns, Select $stmt)
    {
        if (array_key_exists('active', $columns)) {
            $column = $this->dbAdapter->quoteIdentifier("{$this->tableName}.active");

            $stmt->where("{$column} = true");
        }

        if (array_key_exists('deleted', $columns)) {
            $column = $this->dbAdapter->quoteIdentifier("{$this->tableName}.deleted");

            $stmt->where("{$column} = false");
        }

        return $stmt;
    }

    /**
     * Attempt to order the options statement using the options table
     * metadata.  Dewdrop supports two manual sorting columns by
     * convention: sort_index and sort_order.  If either of those
     * columns is present in your options table, the options will be
     * sorted by them.  Otherwise, we'll sort by the title column.
     *
     * @param array $columns The columns portion of the table metadata.
     * @param Select $stmt
     * @return \Dewdrop\Db\Select
     */
    protected function orderStmt(array $columns, Select $stmt)
    {
        $sortColumn = null;

        if (array_key_exists('sort_index', $columns)) {
            $sortColumn = 'sort_index';
        } elseif (array_key_exists('sort_order', $columns)) {
            $sortColumn = 'sort_order';
        }

        if ($sortColumn) {
            $primaryKey = null;

            foreach ($columns as $column => $meta) {
                if ($meta['PRIMARY']) {
                    $primaryKey = $column;
                    break;
                }
            }

            return $stmt->order(
                array(
                    $this->tableName . '.' . $sortColumn,
                    $this->tableName . '.' . $primaryKey
                )
            );
        } elseif ($this->titleColumn instanceof Expr) {
            return $stmt->order($this->titleColumn);
        } else {
            return $stmt->order($this->tableName . '.' . $this->titleColumn);
        }
    }

    /**
     * Load the metadata for the options table.
     *
     * @throws \Dewdrop\Exception
     * @return array
     */
    protected function loadTableMetadata()
    {
        if (!$this->tableName) {
            throw new DewdropException('Table name must be set prior to loading metadata.');
        }

        return $this->dbAdapter->getTableMetadata($this->tableName);
    }
}
