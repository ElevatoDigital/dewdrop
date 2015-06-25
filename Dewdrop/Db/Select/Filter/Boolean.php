<?php

namespace Dewdrop\Db\Select\Filter;

use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\Exception\MissingQueryVar;

class Boolean
{
    private $tableName;

    private $columnName;

    public function __construct($tableName, $columnName)
    {
        $this->tableName  = $tableName;
        $this->columnName = $columnName;
    }

    public function apply(Select $select, $conditionSetName, array $queryVars)
    {
        if (!isset($queryVars['value'])) {
            throw new MissingQueryVar('"value" variable expected.');
        }

        $value = $queryVars['value'];

        // Don't attempt to filter if no value is available
        if (null === $value || '' === $value) {
            return $select;
        }

        return $this->filter($select, $conditionSetName, $value);
    }

    private function filter(Select $select, $conditionSetName, $value)
    {
        $quotedAlias = $select->quoteWithAlias($this->tableName, $this->columnName);

        if ((boolean) $value) {
            return $select->whereConditionSet($conditionSetName, "{$quotedAlias} = true", $value);
        } else {
            return $select->whereConditionSet($conditionSetName, "{$quotedAlias} != true", $value);
        }
    }
}
