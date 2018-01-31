<?php

namespace Dewdrop\Db;

use Dewdrop\Db\Validator\Unique as UniqueValidator;
use Dewdrop\SetOptionsTrait;

class UniqueConstraint
{
    use SetOptionsTrait;

    /**
     * @var Table
     */
    private $table;

    /**
     * @var array
     */
    private $columns;

    /**
     * @var string
     */
    private $validationMessage;

    /**
     * @var bool
     */
    private $caseSensitive = false;

    public function __construct(Table $table, array $columns)
    {
        $this->table   = $table;
        $this->columns = $columns;
    }

    public function setMessage($message)
    {
        return $this->setValidationMessage($message);
    }

    public function setValidationMessage($validationMessage)
    {
        $this->validationMessage = $validationMessage;

        return $this;
    }

    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
    }

    public function affectsColumn($column)
    {
        return in_array($column, $this->columns);
    }

    public function createValidatorForField(Field $field)
    {
        $validator = new UniqueValidator($field, $this);

        if ($this->validationMessage) {
            $validator->setMessage($this->validationMessage, UniqueValidator::NOT_UNIQUE);
        }

        return $validator;
    }

    public function fieldValueIsUnique(Field $field, $value)
    {
        if (!$field->hasRow()) {
            return true;
        }

        $row    = $field->getRow();
        $table  = $row->getTable();
        $db     = $table->getAdapter();
        $select = $db->select();

        $select->from($table->getTableName(), [new Expr('(true)')]);

        $this->filterSelectByFieldValue($select, $field, $value);

        foreach ($this->columns as $column) {
            if ($column === $field->getName()) {
                continue;
            }

            $this->filterSelectByFieldValue($select, $table->field($column), $row->get($column));
        }

        if (!$row->isNew()) {
            foreach ($table->getPrimaryKey() as $column) {
                $quotedColumn = $db->quoteIdentifier($column);
                $select->where("{$quotedColumn} != ?", $row->get($column));
            }
        }

        $valueAlreadyExists = (boolean) $db->fetchOne($select);

        return !($valueAlreadyExists);
    }

    private function filterSelectByFieldValue(Select $select, Field $field, $value)
    {
        if ($field->isType('boolean')) {
            $value = ($value ? 'TRUE' : 'FALSE');
        }

        $quotedFieldName = $select->getAdapter()->quoteIdentifier($field->getName());

        if ($this->caseSensitive || !$field->isType('text')) {
            $select->where("{$quotedFieldName} = ?", $value);
        } else {
            $select->where("LOWER({$quotedFieldName}) = LOWER(?)", $value);
        }

        return $select;
    }
}