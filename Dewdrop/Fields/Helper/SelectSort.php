<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Expr;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Select;
use Dewdrop\Fields;
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
 * $myField->assignHelperCallback(
 *     'SelectSort',
 *     function ($helper, Select $select, $direction) {
 *         return $select->order("a.my_field {$direction}");
 *     }
 * );
 * </code>
 *
 * Note that $direction is guaranteed by the helper to be "ASC" or "DESC", so
 * you don't need to check that yourself.  Your callback does have to return
 * the Select object once it has added the order class.  If you do not return
 * the Select, an exception will be thrown.
 *
 * Also note that in the example the field is specified as "a.my_field" in
 * the ORDER BY clause.  If your callback is likely to used against a range
 * of queries, in which the table aliases may vary, you may want to use
 * \Dewdrop\Db\Select's quoteWithAlias() method.
 */
class SelectSort extends HelperAbstract implements SelectModifierInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'selectsort';

    private $sortedField;

    private $sortedDirection;

    private $defaultField;

    private $defaultDirection = 'ASC';

    private $request;

    private $prefix;

    public function __construct(Request $request)
    {
        $this->request = $request;
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

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setDefaultField(FieldInterface $defaultField)
    {
        $this->defaultField = $defaultField;

        return $this;
    }

    /**
     * Set the default direction that should be used when sorting.
     *
     * @param string $defaultDirection
     * @throws \Dewdrop\Fields\Exception
     * @return SelectSort
     */
    public function setDefaultDirection($defaultDirection)
    {
        $defaultDirection = strtoupper($defaultDirection);

        if ('ASC' !== $defaultDirection && 'DESC' !== $defaultDirection) {
            throw new Exception('Default direction must be ASC or DESC');
        }

        $this->defaultDirection = $defaultDirection;

        return $this;
    }

    /**
     * Given the supplied $fields and \Dewdrop\Request object, find the field
     * referenced in the query string and apply its sort callback to the query.
     *
     * @param Fields $fields
     * @param Select $select
     * @throws \Dewdrop\Fields\Exception
     * @return Select
     */
    public function modifySelect(Fields $fields, Select $select)
    {
        $this->sortedField     = null;
        $this->sortedDirection = null;

        /* @var $field FieldInterface */
        foreach ($fields->getSortableFields() as $field) {
            if ($field->getQueryStringId() === urlencode($this->request->getQuery($this->prefix . 'sort'))) {
                if ('ASC' === $this->defaultDirection) {
                    $dir = ('DESC' === strtoupper($this->request->getQuery($this->prefix . 'dir')) ? 'DESC' : 'ASC');
                } else {
                    $dir = ('ASC' === strtoupper($this->request->getQuery($this->prefix . 'dir')) ? 'ASC' : 'DESC');
                }

                $select = call_user_func(
                    $this->getFieldAssignment($field),
                    $select,
                    $dir
                );

                if (!$select instanceof Select) {
                    throw new Exception('Your SelectSort callback must return the modified Select object.');
                }

                $this->sortedField     = $field;
                $this->sortedDirection = $dir;

                return $select;
            }
        }

        // Sort by the first visible field that is also sortable, if no other sort was performed
        foreach ($fields->getVisibleFields() as $field) {
            if ($field->isSortable() && (null === $this->defaultField || $this->defaultField === $field)) {
                $this->sortedField     = $field;
                $this->sortedDirection = $this->defaultDirection;

                return call_user_func($this->getFieldAssignment($field), $select, $this->defaultDirection);
            }
        }

        return $select;
    }

    /**
     * Check to see if this helper has sorted the Select by a field.
     *
     * @return boolean
     */
    public function isSorted()
    {
        return null !== $this->sortedField;
    }

    /**
     * Get the field the Select is currently sorted by.
     *
     * @return FieldInterface
     */
    public function getSortedField()
    {
        return $this->sortedField;
    }

    /**
     * Get the direction the Select is currently sorted.
     *
     * @return string
     */
    public function getSortedDirection()
    {
        return $this->sortedDirection;
    }

    /**
     * A default implmenetation for database DATE fields.
     *
     * @param DbField $field
     * @param Select $select
     * @param string $direction
     * @return Select
     */
    public function sortDbDate(DbField $field, Select $select, $direction)
    {
        return $select->order("{$field->getName()} $direction");
    }

    /**
     * A default implmenetation for most database fields.
     *
     * @param DbField $field
     * @param Select $select
     * @param string $direction
     * @return Select
     */
    public function sortDbDefault(DbField $field, $select, $direction)
    {
        return $select->order("{$field->getName()} $direction");
    }

    public function sortDbReference(DbField $field, Select $select, $direction)
    {
        return $select->order(
            new Expr(
                "{$select->quoteWithAlias($field->getTable()->getTableName(), $field->getName())} $direction"
            )
        );
    }

    /**
     * Try to detect a default callback for the provided field.  THis helper
     * will only provide a default for database fields of common types.  For
     * custom fields, you'll have to assign your own callback, if you want them
     * to be sortable.
     *
     * @return false|callable
     */
    public function detectCallableForField(FieldInterface $field)
    {
        $method = null;

        if (!$field instanceof DbField) {
            return false;
        }

        if ($field->isType('reference')) {
            $method = 'sortDbReference';
        } elseif ($field->isType('date', 'timestamp')) {
            $method = 'sortDbDate';
        } elseif ($field->isType('manytomany', 'clob', 'string', 'numeric', 'boolean')) {
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
