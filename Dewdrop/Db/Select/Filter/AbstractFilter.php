<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Select\Filter;

/**
 * Abstract filter class
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * The name of the DB column you need to filter.
     *
     * @var string
     */
    protected $columnName;

    /**
     * The name of the table in which the filtered column is present.
     *
     * @var string
     */
    protected $tableName;

    /**
     * Provide the table and column names that will be filtered by this object.
     *
     * @param string $tableName
     * @param string $columnName
     */
    public function __construct($tableName, $columnName)
    {
        $this->tableName  = $tableName;
        $this->columnName = $columnName;
    }
}