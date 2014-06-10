<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Row;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\InputFilter as InputFilterHelper;
use Dewdrop\Request;

class RowLinker
{
    private $rowsByName = array();

    private $errors = array();

    public function __construct(Fields $fields, Request $request, InputFilterHelper $inputFilterHelper = null)
    {
        $this->fields            = $fields;
        $this->request           = $request;
        $this->inputFilterHelper = ($inputFilterHelper ?: new InputFilterHelper());
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    public function apply()
    {
        call_user_func($this->callback, $this, $this->request);
    }

    public function getRow($modelName)
    {
        return $this->rowsByName[$modelName];
    }

    public function isNew()
    {
        $isNew = true;

        foreach ($this->rowsByName as $row) {
            if (!$row->isNew()) {
                $isNew = false;
            }
        }

        return $isNew;
    }

    public function isValid(array $data)
    {
        $inputFilter = $this->inputFilterHelper->getInputFilter();

        foreach ($this->fields->getEditableFields() as $field) {
            if (isset($data[$field->getControlName()])) {
                $field->setValue($data[$field->getControlName()]);
            } elseif ($field instanceof DbField && $field->isType('boolean')) {
                $field->setValue(0);
            }


            $this->inputFilterHelper->addInput($field);
        }

        $inputFilter->setData($data);

        $isValid = $inputFilter->isValid();

        if (!$isValid) {
            $this->errors = $inputFilter->getInvalidInput();
        }

        return $isValid;
    }

    public function hasErrors(FieldInterface $field)
    {
        return array_key_exists($field->getId(), $this->errors);
    }

    public function getErrorMessages(FieldInterface $field)
    {
        return $this->errors[$field->getId()]->getMessages();
    }

    public function save()
    {
        foreach ($this->rowsByName as $row) {
            $row->save();
        }

        return $this;
    }

    public function setRow($modelName, Row $row)
    {
        $model = $this->getModel($modelName);

        if ($model !== $row->getTable()) {
            throw new Exception('The row should be from the same table instance.');
        }

        foreach ($this->fields as $field) {
            if ($field->getTable() === $model) {
                $field->setRow($row);
            }
        }

        $this->rowsByName[$modelName] = $row;

        return $this;
    }

    public function getModel($modelName)
    {
        $models = $this->fields->getModelsByName();

        if (!isset($models[$modelName])) {
            throw new Exception("Could not find model with name '{$modelName}'");
        }

        return $models[$modelName];
    }
}
