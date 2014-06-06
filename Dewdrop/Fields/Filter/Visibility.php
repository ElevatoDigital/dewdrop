<?php

namespace Dewdrop\Fields\Filter;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;

class Visibility
{
    protected $defaultFields = array();

    protected $prefix = '';

    protected $selections;

    protected $dbTableName;

    public function __construct(
        $componentName,
        DbAdapter $dbAdapter,
        Fields $fields,
        $dbTableName = 'dewdrop_visible_fields'
    ) {
        $this->componentName = $componentName;
        $this->dbAdapter     = $dbAdapter;
        $this->fields        = $fields;
        $this->dbTableName   = $dbTableName;
    }

    public function setDefaultFields(array $defaultFields)
    {
        $this->defaultFields = array();

        foreach ($defaultFields as $field) {
            if ($field instanceof FieldInterface) {
                $field = $field->getId();
            }

            $this->defaultFields[] = $field;
        }

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getAllAvailableFields()
    {
        return $this->fields->getVisibleFields();
    }

    public function getVisibleFields($filters = null)
    {
        return $this->fields->getVisibleFields($this);
    }

    public function save(array $selections)
    {
        $visibleFields = array();

        foreach ($selections as $id) {
            if ($this->fields->has($id)) {
                $visibleFields[] = $id;
            }
        }

        if (count($visibleFields)) {
            $this->dbAdapter->beginTransaction();

            $this->dbAdapter->delete(
                $this->dbTableName,
                $this->dbAdapter->quoteInto(
                    'component = ?',
                    $this->componentName
                )
            );

            foreach ($visibleFields as $visibleFieldId) {
                $this->dbAdapter->insert(
                    $this->dbTableName,
                    array(
                        'component' => $this->componentName,
                        'field_id'  => $visibleFieldId
                    )
                );
            }

            $this->dbAdapter->commit();

            // Reset selections so they'll be loaded again when filter is re-applied
            $this->selections = null;
        }
    }

    public function load()
    {
        $selections = $this->dbAdapter->fetchCol(
            sprintf(
                "SELECT field_id FROM %s WHERE component = ?",
                $this->dbAdapter->quoteIdentifier($this->dbTableName)
            ),
            array($this->componentName)
        );

        return is_array($selections) ? $selections : array();
    }

    public function apply(array $fields)
    {
        if (!$this->selections) {
            $this->selections = $this->load();
        }

        if (0 !== count($this->defaultFields)) {
            $defaults = $this->defaultFields;
        } else {
            $defaults = array();

            foreach ($fields as $field) {
                if (4 > count($defaults)) {
                    $defaults[] = $field->getId();
                }
            }
        }

        $output = array();

        foreach ($fields as $id => $field) {
            if (in_array($field->getId(), $this->selections) ||
                (!count($this->selections) && in_array($field->getId(), $defaults))
            ) {
                $output[$id] = $field;
            }
        }

        // If there are no fields found in the output, return the original fields array as a fallback
        if (!count($output)) {
            return $fields;
        }

        return $output;
    }
}
