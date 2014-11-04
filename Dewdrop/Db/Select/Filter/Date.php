<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Select\Filter;

use DateTime;
use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\Exception\InvalidOperator;
use Dewdrop\Db\Select\Filter\Exception\MissingQueryVar;
use Dewdrop\Db\Select\SelectException;
use Dewdrop\Inflector;
use Dewdrop\Pimple;
use Exception;

/**
 * A filter implementation for date (and related datatypes such as timestamps)
 * fields.  This is the most complex filter.  It provides many different operators
 * to help select common date ranges (e.g. "This Month").
 *
 * Internally, this filter divides a lot of its code between operators that require
 * a single input (e.g. OP_IS) and those that require two inputs to check a range
 * of dates (e.g. OP_BETWEEN).
 */
class Date
{
    /**
     * Operator constant for dates in a range (inclusive).
     *
     * @const
     */
    const OP_ON_OR_BETWEEN = 'on-or-between';

    /**
     * Operator constant for dates in a range (exclusive).
     *
     * @const
     */
    const OP_BETWEEN = 'between';

    /**
     * Operator constant for dates before value (inclusive).
     *
     * @const
     */
    const OP_ON_OR_BEFORE = 'on-or-before';

    /**
     * Operator constant for dates before value (exclusive).
     *
     * @const
     */
    const OP_BEFORE = 'before';

    /**
     * Operator constant for dates after value (inclusive).
     *
     * @const
     */
    const OP_ON_OR_AFTER = 'on-or-after';

    /**
     * Operator constant for dates after value (exclusive).
     *
     * @const
     */
    const OP_AFTER = 'after';

    /**
     * Operator constant for exact date match.
     *
     * @const
     */
    const OP_IS = 'is';

    /**
     * Operator constant for today.
     *
     * @const
     */
    const OP_TODAY = 'today';

    /**
     * Operator constant for yesterday.
     *
     * @const
     */
    const OP_YESTERDAY = 'yesterday';

    /**
     * Operator constant for this week.
     *
     * @const
     */
    const OP_THIS_WEEK = 'this-week';

    /**
     * Operator constant for this month.
     *
     * @const
     */
    const OP_THIS_MONTH = 'this-month';

    /**
     * Operator constant for this year.
     *
     * @const
     */
    const OP_THIS_YEAR = 'this-year';

    /**
     * The name of the table in which the filtered column is present.
     *
     * @var string
     */
    private $tableName;

    /**
     * The name of the DB column you need to filter.
     *
     * @var string
     */
    private $columnName;

    /**
     * Inflector object used when converting from operator to filter method name.
     *
     * @var Inflector
     */
    private $inflector;

    /**
     * Whether to cut off time values on timestamps when filtering input.
     *
     * @var bool
     */
    private $truncateTimestamps = true;

    /**
     * Provide the table and column names that will be filtered by this object.
     *
     * @param string $tableName
     * @param string $columnName
     * @param Inflector $inflector
     */
    public function __construct($tableName, $columnName, Inflector $inflector = null)
    {
        $this->tableName   = $tableName;
        $this->columnName  = $columnName;
        $this->inflector   = ($inflector ?: Pimple::getResource('inflector'));
    }

    /**
     * Set whether time values should be truncated from timestamps when filtering
     * input.
     *
     * @param bool $truncateTimestamps
     * @return $this
     */
    public function setTruncateTimestamps($truncateTimestamps)
    {
        $this->truncateTimestamps = $truncateTimestamps;

        return $this;
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

        $startObj = null;
        $startIso = null;
        $endObj   = null;
        $endIso   = null;

        if ($this->truncateTimestamps) {
            $format = 'Y-m-d';
        } else {
            $format = 'Y-m-d G:i:s';
        }

        if ($queryVars['start']) {
            try {
                $startObj = new DateTime($queryVars['start']);
                $startIso = $startObj->format($format);
            } catch (Exception $e) {
                // If we get input we can't parse, we just throw it out
            }
        }

        if ($queryVars['end']) {
            try {
                $endObj = new DateTime($queryVars['end']);
                $endIso = $endObj->format($format);
            } catch (Exception $e) {
                // If we get input we can't parse, we just throw it out
            }
        }

        // If the second input is greater than the first, swap them for the user
        if ($startObj && $endObj && $endObj < $startObj) {
            $swapEndIso = $endIso;
            $endIso     = $startIso;
            $startIso   = $swapEndIso;
        }

        $methodName = 'filter' . $this->inflector->camelize($operator);

        return $this->$methodName($select, $conditionSetName, $startIso, $endIso);
    }

    /**
     * Filter the Select to find records on or between the start and end dates.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param string $start
     * @param string $end
     * @return Select
     */
    public function filterOnOrBetween(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterBetweenInternal(true, $select, $conditionSetName, $start, $end);
    }

    /**
     * Filter the Select to find records between the start and end dates.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param string $start
     * @param string $end
     * @return Select
     */
    public function filterBetween(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterBetweenInternal(false, $select, $conditionSetName, $start, $end);
    }

    /**
     * This method does the heavy-lifting for the two "between" operators (both
     * inclusive and exclusive).  This method will attempt to handle situations
     * where we don't actually have a start or end date by delegating to other
     * methods.
     *
     * @param bool $inclusive
     * @param Select $select
     * @param string $conditionSetName
     * @param string $start
     * @param string $end
     * @return Select
     */
    private function filterBetweenInternal($inclusive, Select $select, $conditionSetName, $start, $end)
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

        $select->whereConditionSet(
            $conditionSetName,
            sprintf(
                '%s AND %s',
                $select->getAdapter()->quoteInto("{$quotedAlias} >{$inclusive} ?", $start),
                $select->getAdapter()->quoteInto("{$quotedAlias} <{$inclusive} ?", $end)
            )
        );

        return $select;
    }

    public function filterBefore(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(false, $select, $conditionSetName, 'before', $start);
    }

    public function filterOnOrBefore(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(true, $select, $conditionSetName, 'before', $start);
    }

    public function filterAfter(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(false, $select, $conditionSetName, 'after', $start);
    }

    public function filterOnOrAfter(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(true, $select, $conditionSetName, 'after', $start);
    }

    public function filterIs(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterSingleInputInternal(false, $select, $conditionSetName, 'equal', $start);
    }

    /**
     * This method does the heavy lifting for all the single-input operators
     * this filter supports.
     *
     * @param bool $inclusive
     * @param Select $select
     * @param string $conditionSetName
     * @param string $type
     * @param string $value
     * @return $this
     * @throws Exception
     * @throws SelectException
     */
    private function filterSingleInputInternal($inclusive, Select $select, $conditionSetName, $type, $value)
    {
        if ('after' !== $type && 'before' !== $type && 'equal' !== $type) {
            throw new SelectException('Expected either "before" or "after" or "equal" as type.');
        }

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

        return $select->whereConditionSet(
            $conditionSetName,
            "{$quotedAlias} {$operator}{$inclusive} ?",
            $value
        );
    }

    private function getAliasForComparison(Select $select)
    {
        $quotedAlias = $select->quoteWithAlias($this->tableName, $this->columnName);
        $dbAdapter   = $select->getAdapter();
        $metadata    = $dbAdapter->getTableMetadata($this->tableName);

        if ($this->truncateTimestamps && 'timestamp' === $metadata['columns'][$this->columnName]['GENERIC_TYPE']) {
            $quotedAlias = $dbAdapter->getDriver()->truncateTimestampToDate($quotedAlias);
        }

        return $quotedAlias;
    }

    /**
     * Ensure the expected input vars were supplied before we attempt to apply
     * a filter.  We need the "comp" variable, which gives us the operator,
     * along with a start and end date.
     *
     * @param array $vars
     * @throws InvalidOperator
     * @throws MissingQueryVar
     */
    private function validate(array $vars)
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
            throw new InvalidOperator("{$vars['comp']} is not a valid operator for date filters.");
        }
    }

    /**
     * Check to see if the supplied operator is valid.
     *
     * @param string $operator
     * @return bool
     */
    private function isValidOperator($operator)
    {
        switch ($operator) {
            case self::OP_ON_OR_BETWEEN:
            case self::OP_BETWEEN:
            case self::OP_ON_OR_BEFORE:
            case self::OP_BEFORE:
            case self::OP_ON_OR_AFTER:
            case self::OP_AFTER:
            case self::OP_IS:
            case self::OP_TODAY:
            case self::OP_YESTERDAY:
            case self::OP_THIS_WEEK:
            case self::OP_THIS_MONTH:
            case self::OP_THIS_YEAR:
                return true;
            default:
                return false;
        }
    }
}
