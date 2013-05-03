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
use Dewdrop\Db\Field;
use Dewdrop\Db\ManyToMany\Field as ManyToManyField;
use Dewdrop\Db\Select;
use Dewdrop\Exception;
use Dewdrop\Paths;

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
     * @var mixed
     */
    protected $titleColumn;

    /**
     * The \Dewdrop\Db\Select that will be used to retrieve the options.
     *
     * @var \Dewdrop\Db\Select
     */
    private $stmt;

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
     * Set one more options on this object.  If a setter is not available
     * for the option designated by keys in your options array, an
     * exception will be thrown.
     *
     * @throws \Dewdrop\Exception
     * @param array $options
     * @return \Dewdrop\Field\OptionPairs
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                throw new Exception("OptionPairs: Unknown option \"{$key}\"");
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
     * @param string $titleColumn
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function setTitleColumn($titleColumn)
    {
        $this->titleColumn = $titleColumn;

        return $this;
    }

    /**
     * Get the \Dewdr\Db\Select that will be used for retrieving options.  If
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

        $stmt
            ->from(
                $this->tableName,
                array(
                    'value' => $this->valueColumn,
                    'title' => $this->titleColumn
                )
            );

        $this->filterStmt($columns, $stmt);
        $this->orderStmt($columns, $stmt);

        return $stmt;
    }

    /**
     * This method is called when no title column has been set prior to the
     * generateStmt() method being called.  If the options table has a "name"
     * or "title" column, those will be used.  If not, we'll use the first
     * CHAR or VARCHAR column found in the table metadata.  If we still have
     * not found a suitable candidate, an exception will be thrown telling
     * the developer to manually specify the title column.
     *
     * @throws \Dewdrop\Exception
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
            if (false !== stripos($meta['DATA_TYPE'], 'char')) {
                return $column;
            }
        }

        throw new Exception('OptionPairs: Title column could not be auto-detected.  Please specify manually.');
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

        throw new Exception('OptionPairs: Could not auto-detect value column.  Please specify manually.');
    }

    /**
     * Attempt to apply some default filters to a generated options
     * statement.  Dewdrop supported two similar conventions here: "active"
     * columns and "deleted" columns.  If your options table has an "active"
     * column, only options for which "active" is true will be included.
     * If your options table has a "deleted" column, options with that
     * column set as true will be excluded.
     *
     * @param arary $columns The columns portion of the table metadata.
     * @param Select $stmt
     * @return \Dewdrop\Db\Select
     */
    protected function filterStmt($columns, Select $stmt)
    {
        if (array_key_exists('active', $columns)) {
            $stmt->where('active = true');
        }

        if (array_key_exists('deleted', $columns)) {
            $stmt->where('deleted = false');
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
            foreach ($columns as $column => $meta) {
                if ($meta['PRIMARY']) {
                    $primaryKey = $column;
                    break;
                }
            }

            return $stmt->order(
                array(
                    $sortColumn,
                    $primaryKey
                )
            );
        }

        return $stmt->order($this->titleColumn);
    }

    /**
     * Load the metadata for the options table.  We do this manually here, but
     * we may eventually move the metadata loading and handling into the DB
     * adapter class itself, if we find this code popping up in many locations.
     *
     * @throws \Dewdrop\Exception
     * @return array
     */
    protected function loadTableMetadata()
    {
        $paths = new Paths();
        $path  = $paths->getModels() . '/metadata/' . $this->tableName . '.php';

        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception("Could not find metadata for table \"{$this->tableName}\"");
        }

        $metadata = require $path;

        return $metadata;
    }
}
