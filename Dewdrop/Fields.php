<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Exception;
use Dewdrop\Fields\Field as CustomField;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\FieldsIterator;

/**
 * The Fields API is at the core of many of Dewdrop's abstractions.  It has two
 * primary goals:
 *
 * 1) Leverage metadata from the database (e.g. information about various
 *    constraints, data-types, etc.) to make working with database fields in
 *    various contexts simpler and less error-prone.
 *
 * 2) To allow the definition of non-DB-related fields as well, so that the
 *    Fields API can be used to inject customizable pieces of code into
 *    logic and rendering loops in a clean way.
 *
 * Adding fields is possible in a few different ways.  You can add a DB field
 * directly from a \Dewdrop\Db\Table model:
 *
 * <code>
 * $fields->add($model->field('my_field'));
 * </code>
 *
 * You can add a custom field by passing an ID string to the add() method and
 * then customizing the field:
 *
 * <code>
 * $fields->add('my_custom_field_id')
 *     ->setLabel('Just a Custom Field')
 *     ->setVisible(true)
 *     ->assignHelperCallback(
 *         'TableCell.Content',
 *         function ($helper, array $rowData) {
 *             return 'Hello, world';
 *         }
 *     );
 * </code>
 *
 * Or, you can instantiate and add the field object directly:
 *
 * <code>
 * $field = new \Dewdrop\Fields\Field();
 *
 * $field
 *     ->setId('my_custom_field_id')
 *     ->setLabel('Just a Custom Field')
 *     ->setVisible(true)
 *     ->assignHelperCallback(
 *         'TableCell.Content',
 *         function ($helper, array $rowData) {
 *             return 'Hello, world';
 *         }
 *     );
 *
 * $fields->add($field);
 * </code>
 *
 * Once added, you can get your fields back in a number of different ways:
 *
 * 1) The DOM-like get(), has(), and remove() methods all take a field ID.
 *
 * 2) The getAll(), getVisibleFields(), getSortableFields(), getFilterableFields()
 *    and getEditableFields() objects all will return new \Dewdrop\Fields objects
 *    that contain only the fields allowed by the method you called.  When calling
 *    any of this methods, you can pass any number of \Dewdrop\Fields\Filter
 *    objects as well to futher sort or limit the fields you get back.
 *
 * 3) You can iterate over the Fields object just like an array.
 *
 * Many other objects in Dewdrop can receive a Fields collection and use it
 * to make decisions.  For example, notable view helpers such as Table can use
 * a collection of fields to render their headers and cells.
 *
 * @see \Dewdrop\Fields\Helper\HelperAbstract
 */
class Fields implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * The fields currently contained in this collection.
     *
     * @var array
     */
    private $fields = array();

    private $modelInstances = array();

    private $modelsByName = array();

    /**
     * An object implementing the \Dewdrop\Fields\UserInterface interface,
     * which can be used to take advantage of the authorization features
     * in the \Dewdrop\Fields API.
     */
    private $user;

    /**
     * Optionally supply an array of fields that can be used as an initial
     * set for this collection.
     *
     * @param array $fields
     */
    public function __construct(array $fields = null)
    {
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $this->add($field);
            }
        }
    }

    public function getIterator()
    {
        return new FieldsIterator($this->fields);
    }

    /**
     * Count the number of fields in this collection.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * Allow addition/replacement of Field objects on this collection via array
     * syntax:
     *
     * <code>
     * $fields['id'] = $field;
     * </code>
     *
     * This is part of the ArrayAccess interface built into PHP.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof FieldInterface) {
            throw new Exception('\Dewdrop\Fields only excepts field objects');
        }

        if (is_string($offset) && !is_number($offset)) {
            $value->setId($offset);

            if ($this->has($offset)) {
                $this->remove($offset);
            }
        }

        $this->add($value);
    }

    /**
     * Get a field by its ID using ArrayAccess syntax.
     *
     * <code>
     * echo $fields['id']->getLabel();
     * </code>
     *
     * This method is part of the ArrayAccess interface built into PHP.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Test to see if the specified field ID exists in this Fields collection
     * using isset():
     *
     * <code>
     * isset($fields['id']);
     * </code>
     *
     * This method is part of the ArrayAccess interface built into PHP.
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Allow removal of a field via ArrayAccess syntax:
     *
     * <code>
     * unset($fields['id']);
     * </code>
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Set the UserInterface object that can be used by the
     * authorization-related features in \Dewdrop\Fields.
     *
     * @param UserInterface $user
     * @return Fields
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Check to see if a field with the given ID exists in this collection.
     *
     * @param string $id
     * @return boolean
     */
    public function has($id)
    {
        foreach ($this->fields as $field) {
            if ($field->getId() === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the field matching the supplied ID from this collection.
     *
     * @param string $id
     * @return FieldInterface
     */
    public function get($id)
    {
        foreach ($this->fields as $field) {
            if ($field->getId() === $id) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Add the supplied field to this collection.  Can be a FieldInterface
     * object or a string, in which case a new custom field will be added
     * with the supplied string as its ID.
     *
     * The newly added FieldInterface object is returned from this method
     * so that it can be further customized immediately, using method
     * chaining.  Once you've completed calling methods on the FieldInterface
     * object itself, you can call any \Dewdrop\Fields methods to return
     * execution to that context.
     *
     * @param mixed $field
     * @return FieldInterface
     */
    public function add($field, $modelName = null)
    {
        if (is_string($field)) {
            $id    = $field;
            $field = new CustomField();
            $field->setId($id);
        }

        if (!$field instanceof FieldInterface) {
            throw new Exception('Field must be a string or instance of \Dewdrop\Fields\FieldInterface');
        }

        if ($field instanceof DbField) {
            $this->handleModelsForDbField($field, $modelName);
        }

        $field->setFieldsSet($this);

        $this->fields[] = $field;

        return $field;
    }

    /**
     * Remove the field with the given ID from this collection.
     *
     * @param string $id
     * @return Fields
     */
    public function remove($id)
    {
        foreach ($this->fields as $index => $field) {
            if ($field->getId() === $id) {
                unset($this->fields[$index]);
                break;
            }
        }

        $this->fields = array_values($this->fields);

        return $this;
    }

    /**
     * Get all the fields currently in this collection.
     *
     * @return Fields
     */
    public function getAll()
    {
        return new Fields($this->fields);
    }

    /**
     * Get any fields that are visible and pass the supplied filters.  Note that
     * you can either pass a single \Dewdrop\Fields\Filter or an array of them.
     *
     * @param mixed $filters
     * @return Fields
     */
    public function getVisibleFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isVisible', $filters);
    }

    /**
     * Get any fields that are sortable and pass the supplied filters.  Note that
     * you can either pass a single \Dewdrop\Fields\Filter or an array of them.
     *
     * @param mixed $filters
     * @return Fields
     */
    public function getSortableFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isSortable', $filters);
    }

    /**
     * Get any fields that are editable and pass the supplied filters.  Note that
     * you can either pass a single \Dewdrop\Fields\Filter or an array of them.
     *
     * @param mixed $filters
     * @return Fields
     */
    public function getEditableFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isEditable', $filters);
    }

    /**
     * Get any fields that are filterable and pass the supplied filters.  Note that
     * you can either pass a single \Dewdrop\Fields\Filter or an array of them.
     *
     * @param mixed $filters
     * @return Fields
     */
    public function getFilterableFields($filters = null)
    {
        return $this->getFieldsPassingMethodCheck('isFilterable', $filters);
    }

    public function getModelsByName()
    {
        return $this->modelsByName;
    }

    protected function handleModelsForDbField(DbField $field, $modelName)
    {
        $fieldTable = $field->getTable();
        $tableName  = $fieldTable->getTableName();
        $tableHash  = spl_object_hash($fieldTable);

        if (null === $modelName &&
            isset($this->modelInstances[$tableName]) &&
            $this->modelInstances[$tableName] !== $fieldTable
        ) {
            throw new Exception(
                'When adding fields from two instances of the same model, you must specify '
                . 'an alternate model name as the second paramter to add().'
            );
        }

        if (null === $modelName) {
            $modelName = $tableName;
        }

        if (isset($this->modelsByName[$modelName]) &&
            $this->modelsByName[$modelName] !== $fieldTable
        ) {
            throw new Exception(
                "The name '{$modelName}' has already been used with another model instance. "
                . 'Please make sure to use model names consistently when adding fields.'
            );
        }

        $this->modelInstances[$tableName] = $fieldTable;
        $this->modelsByName[$modelName]   = $fieldTable;

        // Update the field's control name so that generated IDs, etc., use the new name
        if ($modelName !== $tableName) {
            $field->setControlName($modelName . ':' . $field->getName());
        }
    }

    /**
     * Get any fields that return true when the supplied method is called  and pass
     * the supplied filters.  Note that you can either pass a single
     * \Dewdrop\Fields\Filter or an array of them.
     *
     * @param string $fieldMethodName
     * @param mixed $filters
     * @return Fields
     */
    protected function getFieldsPassingMethodCheck($fieldMethodName, $filters)
    {
        $fields = new Fields();

        foreach ($this->fields as $field) {
            if ($field->$fieldMethodName($this->user)) {
                $fields->add($field);
            }
        }

        return $this->applyFilters($fields, $filters);
    }

    /**
     * Apply the supplied filters to the Fields.  You can pass no filters,
     * a single filter, or an array of filters.
     *
     * @param Fields $fields
     * @param mixed $filters
     * @return Fields
     */
    protected function applyFilters(Fields $fields, $filters)
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
