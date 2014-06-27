<?php

namespace Dewdrop\Db\Select\Filter;

use DateTime;
use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\Exception\InvalidOperator;
use Dewdrop\Db\Select\Filter\Exception\MissingQueryVar;
use Dewdrop\Db\Select\SelectException;
use Dewdrop\Pimple;
use Exception;

class Date
{
    const OP_ON_OR_BETWEEN = 'on-or-between';

    const OP_BETWEEN = 'between';

    const OP_ON_OR_BEFORE = 'on-or-before';

    const OP_BEFORE = 'before';

    const OP_ON_OR_AFTER = 'on-or-after';

    const OP_AFTER = 'after';

    const OP_IS = 'is';

    const OP_TODAY = 'today';

    const OP_YESTERDAY = 'yesterday';

    const OP_THIS_WEEK = 'this-week';

    const OP_THIS_MONTH = 'this-month';

    const OP_THIS_YEAR = 'this-year';

    private $tableName;

    private $columnName;

    private $inflector;

    private $truncateTimestamps = true;

    public function __construct($tableName, $columnName, Inflector $inflector = null)
    {
        $this->tableName   = $tableName;
        $this->columnName  = $columnName;
        $this->inflector   = ($inflector ?: Pimple::getResource('inflector'));
    }

    public function setTruncateTimestamps($truncateTimestamps)
    {
        $this->truncateTimestamps = $truncateTimestamps;

        return $this;
    }

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
            }
        }

        if ($queryVars['end']) {
            try {
                $endObj = new DateTime($queryVars['end']);
                $endIso = $endObj->format($format);
            } catch (Exception $e) {
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

    public function filterOnOrBetween(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterBetweenInternal(true, $select, $conditionSetName, $start, $end);
    }

    public function filterBetween(Select $select, $conditionSetName, $start, $end)
    {
        return $this->filterBetweenInternal(false, $select, $conditionSetName, $start, $end);
    }

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
