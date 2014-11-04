<?php

namespace Dewdrop\Db\Select\Filter;

use Dewdrop\Db\Expr;
use Dewdrop\Db\ManyToMany\Relationship;
use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\Exception\InvalidOperator;
use Dewdrop\Db\Select\Filter\Exception\MissingQueryVar;

class ManyToMany
{
    const OP_CONTAINS = 'contains';

    const OP_NOT_CONTAINS = 'not-contains';

    /**
     * @var Relationship
     */
    private $relationship;

    public function __construct(Relationship $relationship)
    {
        $this->relationship = $relationship;
    }

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
            throw new InvalidOperator("{$operator} is not a valid operator for many-to-many filters.");
        }

        // Don't attempt to filter if no value is available
        if (!$value) {
            return $select;
        }

        return $this->filter($operator, $select, $conditionSetName, $value);
    }

    private function filter($operator, Select $select, $conditionSetName, $value)
    {
        $quotedAlias = $select->quoteWithAlias(
            $this->relationship->getSourceTable()->getTableName(),
            $this->relationship->getSourceColumnName()
        );

        $operator = (self::OP_CONTAINS === $operator ? 'IN' : 'NOT IN');

        return $select->whereConditionSet(
            $conditionSetName,
            "{$quotedAlias} {$operator} (?)",
            new Expr($this->relationship->getFilterSubquery($value))
        );
    }

    private function isValidOperator($operator)
    {
        static $validOperators = array(
            self::OP_CONTAINS,
            self::OP_NOT_CONTAINS
        );

        return in_array($operator, $validOperators);
    }
}
