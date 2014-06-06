<?php

namespace Dewdrop;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Exception;
use Dewdrop\Fields\Field as CustomField;
use Dewdrop\Fields\FieldInterface;

class Fields
{
    private $fields = array();

    private $user;

    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    public function has($id)
    {
        foreach ($this->fields as $field) {
            if ($field->getId() === $id) {
                return true;
            }
        }

        return false;
    }

    public function get($id)
    {
        foreach ($this->fields as $field) {
            if ($field->getId() === $id) {
                return $field;
            }
        }

        return null;
    }

    public function add($field)
    {
        if (is_string($field)) {
            $id    = $field;
            $field = new CustomField();
            $field->setId($id);
        }

        if (!$field instanceof FieldInterface) {
            throw new Exception('Field must be a string or instance of \Dewdrop\Fields\FieldInterface');
        }

        $field->setFieldsSet($this);

        $this->fields[] = $field;

        return $field;
    }

    public function getVisibleFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isVisible', $filters);
    }

    public function getSortableFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isSortable', $filters);
    }

    public function getEditableFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isEditable', $filters);
    }

    public function getFilterableFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isFilterable', $filters);
    }

    protected function getFieldsPassingMethodCheck($fieldMethodName, $filters)
    {
        $fields = array();

        foreach ($this->fields as $field) {
            if ($field->$fieldMethodName($this->user)) {
                $fields[$field->getId()] = $field;
            }
        }

        return $this->applyFilters($fields, $filters);
    }

    protected function applyFilters(array $fields, $filters)
    {
        if (!$filters) {
            return $fields;
        }

        if (!is_array($filters)) {
            $filters = array($filters);
        }

        foreach ($filters as $filter) {
            $fields = $filter->apply($fields);
        }

        return $fields;
    }
}
