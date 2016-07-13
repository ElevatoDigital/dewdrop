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

/**
 * A filter implementation for numeric data types
 */
class Numeric extends AbstractFilter
{
    /**#@+
     * Operators
     */
    const OP_IS           = 'is';
    const OP_IS_BETWEEN   = 'is-between';
    const OP_IS_LESS_THAN = 'is-less-than';
    const OP_IS_MORE_THAN = 'is-more-than';
    /**#@-*/

    /**
     * Apply the filter to the supplied Select object.
     *
     * @param Select $select
     * @param string $conditionSetName
     * @param array $queryVars
     * @return Select
     * @throws InvalidOperator
     */
    public function apply(Select $select, $conditionSetName, array $queryVars)
    {
        $this->ensurePresenceOfRequiredQueryVars($queryVars);

        $expression = $this->getComparisonExpression($select);

        $op1 = trim($queryVars['operand1']);
        $op2 = trim($queryVars['operand2']);

        switch ($queryVars['comp']) {
            case static::OP_IS:
                if ('' === $op1) {
                    return $select;
                }
                return $select->whereConditionSet($conditionSetName, "{$expression} = ?", $op1);
            case static::OP_IS_BETWEEN:
                if ('' === $op1 && '' === $op2) {
                    return $select;
                } elseif ('' === $op1) {
                    return $select->whereConditionSet($conditionSetName, "{$expression} <= ?", $op2);
                } elseif ('' === $op2) {
                    return $select->whereConditionSet($conditionSetName, "{$expression} >= ?", $op1);
                } else {
                    if ($op1 > $op2) {
                        $op1Temp = $op1;
                        $op1     = $op2;
                        $op2     = $op1Temp;
                    }
                    $db = $select->getAdapter();
                    return $select->whereConditionSet(
                        $conditionSetName,
                        sprintf(
                            "{$expression} BETWEEN %s AND %s",
                            $db->quote($op1),
                            $db->quote($op2)
                        )
                    );
                }
            case static::OP_IS_LESS_THAN:
                if ('' === $op1) {
                    return $select;
                }
                return $select->whereConditionSet($conditionSetName, "{$expression} < ?", $op1);
            case static::OP_IS_MORE_THAN:
                if ('' === $op1) {
                    return $select;
                }
                return $select->whereConditionSet($conditionSetName, "{$expression} > ?", $op1);
            default:
                throw new InvalidOperator("{$queryVars['comp']} is not a valid operator for numeric filters.");
        }
    }

    /**
     * Ensure the expected input vars were supplied before we attempt to apply
     * a filter.  We need the "comp" variable, which gives us the operator,
     * along with two operands.
     *
     * @param array $queryVars
     * @throws MissingQueryVar
     */
    protected function ensurePresenceOfRequiredQueryVars(array $queryVars)
    {
        foreach (['comp', 'operand1', 'operand2'] as $varName) {
            if (!isset($queryVars[$varName])) {
                throw new MissingQueryVar("\"{$varName}\" variable expected.");
            }
        }
    }
}
