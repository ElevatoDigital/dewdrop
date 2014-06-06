<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Select;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Request;

/**
 * This helper allow you to sort a \Dewdrop\Db\Select object by leveraging
 * the Field API.  In the case of database-related fields, this helper will
 * auto-detect the data type and other information from the DB schema to
 * determine a reasonable default approach for sorting.  For other fields,
 * you will have to specify a custom sort callback.
 *
 * When defining a custom callback for this helper, use the following
 * callback parameters:
 *
 * <code>
 *
 * </code>
 *
 */
class SelectSort extends HelperAbstract
{
    protected $name = 'selectsort';

    public function __construct(DbAdapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    public function sortByRequest(array $fields, Select $select, Request $request, $paramPrefix = '')
    {
        foreach ($fields as $field) {
            if ($field->getId() === $request->getQuery($paramPrefix . 'sort')) {
                $direction = ('desc' === $request->getQuery($paramPrefix . 'dir') ? 'desc' : 'asc');

                $select = call_user_func(
                    $this->getFieldAssignment($field),
                    $select,
                    $direction
                );

                if (!$select instanceof Select) {
                    throw new Exception('You SelectSort callback must return the modified Select object.');
                }

                return $select;
            }
        }

        return $select;
    }

    public function sortDbDate(DbField $field, Select $select, $direction)
    {
        return $select->order("{$field->getName()} $direction");
    }

    public function sortDbDefault(DbField $field, $select, $direction)
    {
        return $select->order("{$field->getName()} $direction");
    }

    public function detectCallableForField(FieldInterface $field)
    {
        $method = null;

        if (!$field instanceof DbField) {
            return false;
        }

        if ($field->isType('boolean')) {
            $method = 'sortDbBoolean';
        } elseif ($field->isType('reference')) {
            $method = 'sortDbReference';
        } elseif ($field->isType('date', 'timestamp')) {
            $method = 'sortDbDate';
        } elseif ($field->isType('manytomany', 'clob', 'string', 'numeric')) {
            $method = 'sortDbDefault';
        }

        if (!$method) {
            return false;
        } else {
            return function ($helper, Select $select, $direction) use ($field, $method) {
                return $this->$method($field, $select, $direction);
            };
        }
    }
}
