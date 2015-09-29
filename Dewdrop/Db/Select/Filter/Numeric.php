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
        $this->validateQueryVars($queryVars);

        $quotedAlias = $select->quoteWithAlias($this->tableName, $this->columnName);

        switch ($queryVars['comp']) {
            case static::OP_IS:
                return $select->whereConditionSet($conditionSetName, "{$quotedAlias} = ?", $queryVars['operand1']);
            case static::OP_IS_BETWEEN:
                $op1 = trim($queryVars['operand1']);
                $op2 = trim($queryVars['operand2']);
                if ('' === $op1 && '' === $op2) {
                    return $select;
                } elseif ('' === $op1) {
                    return $select->whereConditionSet($conditionSetName, "{$quotedAlias} <= ?", $queryVars['operand2']);
                } elseif ('' === $op2) {
                    return $select->whereConditionSet($conditionSetName, "{$quotedAlias} >= ?", $queryVars['operand1']);
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
                            "{$quotedAlias} BETWEEN %s AND %s",
                            $db->quote($op1),
                            $db->quote($op2)
                        )
                    );
                }
            case static::OP_IS_LESS_THAN:
                return $select->whereConditionSet($conditionSetName, "{$quotedAlias} < ?", $queryVars['operand1']);
            case static::OP_IS_MORE_THAN:
                return $select->whereConditionSet($conditionSetName, "{$quotedAlias} > ?", $queryVars['operand1']);
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
    protected function validateQueryVars(array $queryVars)
    {
        if (!isset($queryVars['comp'])) {
            throw new MissingQueryVar('"comp" variable expected.');
        }

        if (!isset($queryVars['operand1'])) {
            throw new MissingQueryVar('"operand1" variable expected.');
        }

        if (!isset($queryVars['operand2'])) {
            throw new MissingQueryVar('"operand2" variable expected.');
        }
    }
}