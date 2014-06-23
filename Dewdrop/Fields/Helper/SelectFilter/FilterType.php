<?php

namespace Dewdrop\Fields\Helper\SelectFilter;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;

class FilterType extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'selectfilter.filtertype';

    public function getTypeAndRelatedOptions(FieldInterface $field)
    {
        return call_user_func($this->getFieldAssignment($field));
    }

    public function detectCallableForField(FieldInterface $field)
    {
        $method = null;
        $type   = null;

        if (!$field instanceof DbField) {
            return false;
        }

        if ($field->isType('reference')) {
            return $this->handleDbReference($field);
        }

        if ($field->isType('boolean')) {
            $type = 'boolean';
        } elseif ($field->isType('date', 'timestamp')) {
            $type = 'date';
        } elseif ($field->isType('integer', 'float')) {
            $type = 'numeric';
        } elseif ($field->isType('clob', 'text')) {
            $type = 'text';
        } else {
            return false;
        }

        if (null !== $type) {
            return function () use ($type) {
                return array(
                    'type'    => $type,
                    'options' => ''
                );
            };
        }
    }

    protected function handleDbReference(DbField $field)
    {
        return function () use ($field) {
            return array(
                'type'    => 'reference',
                'options' => array(
                    'options' => $field->getOptionPairs()->fetch()
                )
            );
        };
    }
}
