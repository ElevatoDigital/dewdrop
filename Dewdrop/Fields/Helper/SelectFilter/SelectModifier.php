<?php

namespace Dewdrop\Fields\Helper\SelectFilter;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Eav\Field as EavField;
use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\ManyToMany as ManyToManyFilter;
use Dewdrop\Fields;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;
use Dewdrop\Fields\Helper\SelectModifierInterface;
use Dewdrop\Request;

class SelectModifier extends HelperAbstract implements SelectModifierInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'selectfilter.selectmodifier';

    private $request;

    private $prefix;

    private $customFilters = array();

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Replace the request on this helper.  Mostly useful during testing.
     *
     * @param Request $request
     * @return SelectSort
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function getConditionSetConjunction()
    {
        if ('any' === $this->request->getQuery($this->prefix . 'ftr-logic')) {
            return Select::SQL_OR;
        } else {
            return Select::SQL_AND;
        }
    }

    public function getFilteredFieldIds()
    {
        $ids     = array();
        $pattern = '/' . preg_quote($this->prefix) . 'ftr-id/i';

        foreach ($this->request->getQuery() as $key => $value) {
            if (preg_match($pattern, $key)) {
                $index = (int) strrev($key);

                $ids[$index] = $value;
            }
        }

        return $ids;
    }

    /**
     * @todo Add type hint for filter param once interface is worked out
     */
    public function addCustomFilter($filter, array $vars)
    {
        $this->customFilters[] = array(
            'filter' => $filter,
            'vars'   => $vars
        );

        return $this;
    }

    public function getCustomFilters()
    {
        return $this->customFilters;
    }

    public function getFilterVars($index)
    {
        $vars = array();

        $indexPattern  = '/\_' . preg_quote($index) . '$/i';
        $prefixPattern = '/^' . preg_quote($this->prefix) . 'ftr-/i';

        foreach ($this->request->getQuery() as $key => $value) {
            if (preg_match($indexPattern, $key)) {
                $var = preg_replace(array($prefixPattern, $indexPattern), '', $key);

                $vars[$var] = $value;
            }
        }

        return $vars;
    }

    public function getCurrentFilters()
    {
        $out = array();

        foreach ($this->getFilteredFieldIds() as $index => $id) {
            $out[] = $this->getFilterVars($index);
        }

        return $out;
    }

    public function modifySelect(Fields $fields, Select $select)
    {
        $conditionSetName = $this->prefix . 'filters';

        $select->registerConditionSet(
            $conditionSetName,
            $this->getConditionSetConjunction()
        );

        foreach ($this->getFilteredFieldIds() as $index => $id) {
            $urlId = urlencode($id);

            foreach ($fields as $field) {
                if ($urlId === $field->getQueryStringId()) {
                    $callback = $this->getFieldAssignment($field);

                    $select = call_user_func(
                        $callback,
                        $select,
                        $conditionSetName,
                        $this->getFilterVars($index)
                    );
                }
            }
        }

        foreach ($this->customFilters as $filter) {
            $select = $filter['filter']->apply($select, $conditionSetName, $filter['vars']);
        }

        return $select;
    }

    public function detectCallableForField(FieldInterface $field)
    {
        $type = null;

        if (!$field instanceof DbField) {
            return false;
        }

        if ($field->isType('manytomany')) {
            /* @var $field \Dewdrop\Db\ManyToMany\Field */
            $filter = new ManyToManyFilter($field->getManyToManyRelationship());
        } else {
            if ($field->isType('reference')) {
                $type = 'Reference';
            } elseif ($field->isType('boolean')) {
                $type = 'Boolean';
            } elseif ($field->isType('date', 'timestamp')) {
                $type = 'Date';
            } elseif ($field->isType('integer', 'float')) {
                $type = 'Numeric';
            } elseif ($field->isType('clob', 'text')) {
                $type = 'Text';
            } else {
                return false;
            }

            if ($field instanceof EavField) {
                $tableName = $field->getName();
                $fieldName = 'value';
            } else {
                $tableName = $field->getTable()->getTableName();
                $fieldName = $field->getName();
            }

            $className = '\Dewdrop\Db\Select\Filter\\' . $type;
            $filter    = new $className($tableName, $fieldName);
        }

        return function ($helper, $select, $conditionSetName, $queryVars) use ($filter) {
            return $filter->apply($select, $conditionSetName, $queryVars);
        };
    }
}
