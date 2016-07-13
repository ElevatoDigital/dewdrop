<?php

namespace Dewdrop\Db\Select\Filter;

use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\Exception\InvalidOperator;
use Dewdrop\Db\Select\Filter\Exception\MissingQueryVar;

class Text extends AbstractFilter
{
    const OP_CONTAINS = 'contains';

    const OP_NOT_CONTAINS = 'does-not-contain';

    const OP_STARTS_WITH = 'starts-with';

    const OP_ENDS_WITH = 'ends-with';

    public function apply(Select $select, $conditionSetName, array $queryVars)
    {
        if (!isset($queryVars['comp'])) {
            throw new MissingQueryVar('"comp" variable expected.');
        }

        if (!isset($queryVars['value'])) {
            throw new MissingQueryVar('"value" variable expected.');
        }

        $operator = $queryVars['comp'];
        $value    = $queryVars['value'];

        if (!$this->isValidOperator($operator)) {
            throw new InvalidOperator("{$operator} is not a valid operator for text filters.");
        }

        // Don't attempt to filter if no value is available
        if ('' === (string) $value) {
            return $select;
        }

        static $filterMethods = array(
            self::OP_CONTAINS     => 'filterContains',
            self::OP_NOT_CONTAINS => 'filterNotContains',
            self::OP_STARTS_WITH  => 'filterStartsWith',
            self::OP_ENDS_WITH    => 'filterEndsWith'
        );

        $method = $filterMethods[$operator];

        return $this->$method($select, $conditionSetName, $value);
    }

    private function filterContains(Select $select, $conditionSetName, $value)
    {
        $expression = $this->getComparisonExpression($select);
        $operator   = $select->getAdapter()->getDriver()->getCaseInsensitiveLikeOperator();

        return $select->whereConditionSet(
            $conditionSetName,
            "{$expression} {$operator} ?",
            '%' . $value . '%'
        );
    }

    private function filterNotContains(Select $select, $conditionSetName, $value)
    {
        $expression = $this->getComparisonExpression($select);
        $operator   = $select->getAdapter()->getDriver()->getCaseInsensitiveLikeOperator();

        return $select->whereConditionSet(
            $conditionSetName,
            "{$expression} NOT {$operator} ?",
            '%' . $value . '%'
        );
    }

    private function filterStartsWith(Select $select, $conditionSetName, $value)
    {
        $expression = $this->getComparisonExpression($select);
        $operator   = $select->getAdapter()->getDriver()->getCaseInsensitiveLikeOperator();

        return $select->whereConditionSet(
            $conditionSetName,
            "{$expression} {$operator} ?",
            $value . '%'
        );
    }

    private function filterEndsWith(Select $select, $conditionSetName, $value)
    {
        $expression = $this->getComparisonExpression($select);
        $operator   = $select->getAdapter()->getDriver()->getCaseInsensitiveLikeOperator();

        return $select->whereConditionSet(
            $conditionSetName,
            "{$expression} {$operator} ?",
            '%' . $value
        );
    }

    private function isValidOperator($operator)
    {
        static $validOperators = array(
            self::OP_CONTAINS,
            self::OP_NOT_CONTAINS,
            self::OP_STARTS_WITH,
            self::OP_ENDS_WITH
        );

        return in_array($operator, $validOperators);
    }
}
