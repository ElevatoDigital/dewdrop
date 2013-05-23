<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Countable;
use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Exception;
use Iterator;

/**
 * This object can be used to dynamically sort the fields in a
 * \Dewdrop\Fields\Edit object.  For example, if you have a form editor
 * that allows the user to add and remove custom fields and also specify
 * the order in which those fields should be displayed, you could use
 * the SortedSet class to store and then sort fields in that order.
 *
 * In your view scripts, the SortedSet can be used much like a normal
 * \Dewdrop\Fields\Edit object, so you can iterate over the set in a
 * foreach loop, retrieve a single field with get() and check for a
 * field's presence with has().
 *
 * If no sort order has been saved yet, the SortedSet will return its
 * fields in the order they were added to the \Dewdrop\Fields\Edit object.
 * To save a new order for the set, just call the save() method with an
 * array of field control names in the order you'd like them displayed.
 * Any fields that do not have a sort value in dewdrop_fieldset_fields will
 * be appended to the end during iteration.
 */
class SortedSet implements Countable, Iterator
{
    /**
     * The name of the fieldset in dewdrop_fieldsets that will be used
     * for retrieving and saving sort order values for these fields.
     *
     * @var $name
     */
    private $name;

    /**
     * The \Dewdrop\Fields\Edit collection that will be sorted.
     *
     * @var \Dewdrop\Fields\Edit
     */
    private $fields;

    /**
     * The DB adapter that will be used to retrieve the sort order for this
     * set.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $db;

    /**
     * A sorted array of the field objects in this set.  This array is populated
     * when the load() method is called and then is accessed via the iterator
     * interface (i.e. by looping over this object in a foreach).
     *
     * @var array
     */
    private $sortedFields;

    /**
     * Index used for iterator interface.
     *
     * @var integer
     */
    private $currentIndex = 0;

    /**
     * Use the supplied name-value options array to initialize the
     * required options for this SortedSet object: db, name, and fields.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     * Set multiple options on this object using the supplied name-value
     * array.
     *
     * @param array $options
     * @return \Dewdrop\Fields\SortedSet
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                throw new Exception("Fields\Groups: Attempting to set unknown option \"{$name}\"");
            }
        }

        return $this;
    }

    /**
     * Set the name of the fieldset in the dewdrop_fieldsets DB table that will
     * be used when retrieving and saving sort order values for this set.
     *
     * @param string $name
     * @return \Dewdrop\Fields\SortedSet
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the \Dewdrop\Fields\Edit object that will supply the actual field
     * objects that need to be sorted to this set.
     *
     * @param Edit $fields
     * @return \Dewdrop\Fields\SortedSet
     */
    public function setFields(Edit $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the \Dewdrop\Db\Adapter object that will be used to retrieve and
     * save values back to the dewdrop_fieldset_fields table in the database.
     *
     * @param DbAdapter $db
     * @return \Dewdrop\Fields\SortedSet
     */
    public function setDb(DbAdapter $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Get the field matching the supplied control name.
     *
     * We retrieve the field from the \Dewdrop\Fields\Edit object assigned
     * to this set.  This method is provided to make use of SortedSet and Edit
     * in view scripts consistent.
     *
     * @param string $controlName
     * @return \Dewdrop\Db\Field
     */
    public function get($controlName)
    {
        return $this->fields->get($controlName);
    }

    /**
     * Check to see if this object's fields container has a reference to the
     * field with the provided control name.  This method is present primarily
     * to ensure that a SortedSet and a \Dewdrop\Fields\Edit object can be used
     * interchangably in a view script.
     *
     * @param string $controlName
     * @return boolean
     */
    public function has($controlName)
    {
        return $this->fields->has($controlName);
    }

    /**
     * Save the sort index values for this set using the supplied array of field
     * control names.  The control names should be in the order you want them
     * saved in the database.  Subsequent uses of this set will sort its fields
     * according the saved order.  If the fieldset is not yet present in
     * dewdrop_fieldsets, it will be automatically inserted during this method.
     *
     * @param array $sortedFieldNames
     * @return \Dewdrop\Fields\SortedSet
     */
    public function save(array $sortedFieldNames)
    {
        $this->db->query('START TRANSACTION');

        $this->db->delete(
            'dewdrop_fieldset_fields',
            $this->db->quoteInto('fieldset_name = ?', $this->name)
        );

        $sql = $this->db->quoteInto(
            'SELECT true FROM dewdrop_fieldsets WHERE fieldset_name = ?',
            $this->name
        );

        if (!$this->db->fetchOne($sql)) {
            $this->db->insert('dewdrop_fieldsets', array('fieldset_name' => $this->name));
        }

        $sortIndex = 0;

        foreach ($sortedFieldNames as $fieldName) {
            $this->db->insert(
                'dewdrop_fieldset_fields',
                array(
                    'field_name'    => $fieldName,
                    'fieldset_name' => $this->name,
                    'sort_index'    => $sortIndex
                )
            );

            $sortIndex += 1;
        }

        $this->db->query('COMMIT');
    }

    /**
     * Get a count of the fields added to this collection.
     *
     * @return integer
     */
    public function count()
    {
        $this->load();

        return count($this->sortedFields);
    }

    /**
     * Retrieve the current field during iteration.
     *
     * @return \Dewdrop\Db\Field
     */
    public function current()
    {
        $this->load();

        $fields = array_values($this->sortedFields);

        return $fields[$this->currentIndex];
    }

    /**
     * Return the current index during iteration.
     *
     * @return integer
     */
    public function key()
    {
        return $this->currentIndex;
    }

    /**
     * Advance to the next index during iteration.
     *
     * @return void
     */
    public function next()
    {
        $this->currentIndex += 1;
    }

    /**
     * Seek to the previous index.
     *
     * @return void
     */
    public function prev()
    {
        $this->currentIndex -= 1;
    }

    /**
     * Return the iteration index to the initial position.
     *
     * @return void
     */
    public function rewind()
    {
        $this->currentIndex = 0;
    }

    /**
     * Test to see if an item is present at the current index during
     * iteration.
     *
     * @return boolean
     */
    public function valid()
    {
        $this->load();

        $fields = array_values($this->sortedFields);

        return array_key_exists($this->currentIndex, $fields);
    }

    /**
     * Load the $sortedFields array.  When you begin iterating over this
     * SortedSet, this method will be called to populate the $sortedFields
     * property with an array of field objects in the order specified for
     * this set.  If a field doesn't have an order specified explicitly
     * in dewdrop_fieldset_fields, it will be appended to the end of the
     * list.
     *
     * @return array
     */
    private function load()
    {
        if (!$this->sortedFields) {
            $sortedFields = array();
            $namesInOrder = $this->fetchSortedFieldNames();

            foreach ($namesInOrder as $name) {
                if ($this->fields->has($name)) {
                    $sortedFields[$name] = $this->fields->get($name);
                }
            }

            foreach ($this->fields as $field) {
                if (!array_key_exists($field->getControlName(), $sortedFields)) {
                    $sortedFields[$field->getControlName()] = $field;
                }
            }

            $this->sortedFields = $sortedFields;
        }

        return $this->sortedFields;
    }

    /**
     * Retrieve a sorted list of field control names from
     * dewdrop_fieldset_fields.
     *
     * @return array
     */
    private function fetchSortedFieldNames()
    {
        $stmt = $this->db->select();

        $stmt
            ->from('dewdrop_fieldset_fields', array('field_name'))
            ->where('fieldset_name = ?', $this->name)
            ->order('sort_index');

        return $this->db->fetchCol($stmt);
    }
}
