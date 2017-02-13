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
use Dewdrop\Db\Select\Filter\Exception\InvalidOperator;
use Dewdrop\Db\Select\Filter\Exception\MissingQueryVar;
use Dewdrop\Inflector;
use Dewdrop\Pimple;
use Exception;

/**
 * A filter implementation for time fields.
 */
class Time
{
    /**
     * Operator constant for times in a range (inclusive).
     *
     * @const
     */
    const OP_ON_OR_BETWEEN = 'on-or-between';

    /**
     * Operator constant for times in a range (exclusive).
     *
     * @const
     */
    const OP_BETWEEN = 'between';

    /**
     * Operator constant for times before value (inclusive).
     *
     * @const
     */
    const OP_ON_OR_BEFORE = 'on-or-before';

    /**
     * Operator constant for times before value (exclusive).
     *
     * @const
     */
    const OP_BEFORE = 'before';

    /**
     * Operator constant for times after value (inclusive).
     *
     * @const
     */
    const OP_ON_OR_AFTER = 'on-or-after';

    /**
     * Operator constant for times after value (exclusive).
     *
     * @const
     */
    const OP_AFTER = 'after';

    /**
     * Operator constant for exact time match.
     *
     * @const
     */
    const OP_IS = 'is';

    /**
     * The name of the table in which the filtered column is present.
     *
     * @var string
     */
    protected $tableName;

    /**
     * The name of the DB column you need to filter.
     *
     * @var string
     */
    protected $columnName;

    /**
     * Inflector object used when converting from operator to filter method name.
     *
     * @var Inflector
     */
    protected $inflector;

    /**
     * Provide the table and column names that will be filtered by this object.
     *
     * @param string $tableName
     * @param string $columnName
     * @param Inflector $inflector
     */
    public function __construct($tableName, $columnName, Inflector $inflector = null)
    {
        $this->tableName  = $tableName;
        $this->columnName = $columnName;
        $this->inflector  = ($inflector ?: Pimple::getResource('inflector'));
    }

    /**
     * Apply the filter to the supplied Select object.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param array $queryVars
     * @return Select
     * @throws InvalidOperator
     * @throws MissingQueryVar
     */
    public function apply(Select $select, $conditionSetName, array $queryVars)
    {
        $this->validate($queryVars);

        $operator = $queryVars['comp'];

        $start = null;
        $end   = null;

        static $format = 'G:i:s';

        if ($queryVars['start']) {
            $start = date($format, strtotime($queryVars['start']));
        }

        if ($queryVars['end']) {
            $end = date($format, strtotime($queryVars['end']));
        }

        // If the second input is greater than the first, swap them for the user
        if ($start && $end && $end < $start) {
            $endOld = $end;
            $end    = $start;
            $start  = $endOld;
        }

        $methodName = 'filter' . $this->inflector->camelize($operator);

        return $this->$methodName($select, $conditionSetName, $start, $end);
    }

    /**
     * Filter the Select to find records on or between the start and end times.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterOnOrBetween(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterBetweenInternal(true, $select, $conditionSetName, $start, $end);
    }

    /**
     * Filter the Select to find records between the start and end times.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterBetween(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterBetweenInternal(false, $select, $conditionSetName, $start, $end);
    }

    /**
     * This method does the heavy-lifting for the two "between" operators (both inclusive and exclusive).  This method
     * will attempt to handle situations where we don't actually have a start or end time by delegating to other
     * methods.
     *
     * @param bool $inclusive
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    protected function filterBetweenInternal($inclusive, Select $select, $conditionSetName, $start, $end)
    {
        if (!$start && !$end) {
            return $select;
        } elseif (!$start && $inclusive) {
            return $this->filterOnOrBefore($select, $conditionSetName, $end, $start);
        } elseif (!$start && !$inclusive) {
            return $this->filterBefore($select, $conditionSetName, $end, $start);
        } elseif (!$end && $inclusive) {
            return $this->filterOnOrAfter($select, $conditionSetName, $start, $end);
        } elseif (!$end) {
            return $this->filterAfter($select, $conditionSetName, $start, $end);
        }

        $quotedAlias = $this->getAliasForComparison($select);
        $inclusive   = ($inclusive ? '=' : '');

        return $select->whereConditionSet(
            $conditionSetName,
            sprintf(
                '%s AND %s',
                $select->getAdapter()->quoteInto("{$quotedAlias} >{$inclusive} ?", $start),
                $select->getAdapter()->quoteInto("{$quotedAlias} <{$inclusive} ?", $end)
            )
        );
    }

    /**
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterBefore(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(false, $select, $conditionSetName, 'before', $start);
    }

    /**
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterOnOrBefore(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(true, $select, $conditionSetName, 'before', $start);
    }

    /**
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterAfter(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(false, $select, $conditionSetName, 'after', $start);
    }

    /**
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterOnOrAfter(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(true, $select, $conditionSetName, 'after', $start);
    }

    /**
     * @param Select $select
     * @param string $conditionSetName
     * @param string|null $start
     * @param string|null $end
     * @return Select
     */
    public function filterIs(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(false, $select, $conditionSetName, 'equal', $start);
    }

    /**
     * This method does the heavy lifting for all the single-input operators this filter supports.
     *
     * @param bool $inclusive
     * @param Select $select
     * @param string $conditionSetName
     * @param string $type
     * @param string|null $value
     * @return Select
     * @throws Exception
     */
    protected function filterSingleInputInternal($inclusive, Select $select, $conditionSetName, $type, $value)
    {
        $quotedAlias = $this->getAliasForComparison($select);
        $inclusive   = ($inclusive ? '=' : '');

        switch ($type) {
            case 'before':
                $operator = '<';
                break;
            case 'after':
                $operator = '>';
                break;
            case 'equal':
                $operator = '=';
                break;
            default:
                throw new Exception("filterSingleInputInternal expects before, after or equal operators only.");
                break;
        }

        return $select->whereConditionSet($conditionSetName, "{$quotedAlias} {$operator}{$inclusive} ?", $value);
    }

    /**
     * @param Select $select
     * @return string
     */
    protected function getAliasForComparison(Select $select)
    {
        return $select->quoteWithAlias($this->tableName, $this->columnName);
    }

    /**
     * Ensure the expected input vars were supplied before we attempt to apply a filter.  We need the "comp" variable,
     * which gives us the operator, along with start and end times.
     *
     * @param array $vars
     * @throws InvalidOperator
     * @throws MissingQueryVar
     */
    protected function validate(array $vars)
    {
        if (!isset($vars['comp'])) {
            throw new MissingQueryVar('"comp" variable expected.');
        }

        if (!isset($vars['start'])) {
            throw new MissingQueryVar('"start" variable expected.');
        }

        if (!isset($vars['end'])) {
            throw new MissingQueryVar('"end" variable expected.');
        }

        if (!$this->isValidOperator($vars['comp'])) {
            throw new InvalidOperator("{$vars['comp']} is not a valid operator for time filters.");
        }
    }

    /**
     * Check to see if the supplied operator is valid.
     *
     * @param string $operator
     * @return bool
     */
    protected function isValidOperator($operator)
    {
        switch ($operator) {
            case self::OP_ON_OR_BETWEEN:
            case self::OP_BETWEEN:
            case self::OP_ON_OR_BEFORE:
            case self::OP_BEFORE:
            case self::OP_ON_OR_AFTER:
            case self::OP_AFTER:
            case self::OP_IS:
                return true;
            default:
                return false;
        }
    }
}
