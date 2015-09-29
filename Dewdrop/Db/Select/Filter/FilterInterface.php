<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Select\Filter;

use Dewdrop\Db\Select;

/**
 * Filter interface
 */
interface FilterInterface
{
    /**
     * Provide the table and column names that will be filtered by this object.
     *
     * @param string $tableName
     * @param string $columnName
     */
    public function __construct($tableName, $columnName);

    /**
     * Apply the filter to the supplied Select object.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param array $queryVars
     * @return Select
     */
    public function apply(Select $select, $conditionSetName, array $queryVars);
}