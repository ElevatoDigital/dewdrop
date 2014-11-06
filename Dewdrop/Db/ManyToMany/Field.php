<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\ManyToMany;

use Dewdrop\Db\Field as BaseField;
use Zend\InputFilter\Input;

/**
 * This class enables you to leverage the Db\Field API for many-to-many
 * relationships.  For example, if you have a "projects" table that
 * has a many-to-many relationship with a "staff" stable, represented
 * by the cross-reference table "project_staff", then you would call
 * hasMany('staff, 'project_staff') on your Projects Table class.  Once
 * the relationship has been registered with hasMany(), you can set and
 * get values for the "staff" field on row objects.  Also, you can pass
 * the field to view helpers to generate controls, like this:
 *
 * <pre>
 * $this->wpCheckboxList($this->fields->get('staff'));
 * </pre>
 */
class Field extends BaseField
{
    /**
     * Whether this field is required.  By default, we say that ManyToMany
     * fields are not required, meaning that the array of values assigned
     * to the field does not need to have any elements in it.
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * The ManyToMany relationship object that is used by this field to
     * get options, etc.
     *
     * @var \Dewdrop\Db\ManyToMany\Relationship
     */
    private $manyToManyRelationship;

    /**
     * Check to see if this field is required.  In the case of ManyToMany
     * fields, this refers to the number of elements in the value array,
     * not any concrete DB column value.  So, we do not consult DB metadata
     * to see if a column is NULLable or not like we do in normal Field objects.
     * Instead, you have to manually set the field as required, if that is
     * desired.
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Set the ManyToMany relationship that is used by this field to manage
     * values and options for this field.
     *
     * @param Relationship $manyToManyRelationship
     * @return \Dewdrop\Db\ManyToMany\Field
     */
    public function setManyToManyRelationship(Relationship $manyToManyRelationship)
    {
        $this->manyToManyRelationship = $manyToManyRelationship;

        return $this;
    }

    /**
     * Return a reference to the field's many-to-many relationship.  Allows
     * reconfiguration of the relationship, if needed, and can also be useful
     * during testing.
     *
     * @return \Dewdrop\Db\ManyToMany\Relationship
     */
    public function getManyToManyRelationship()
    {
        return $this->manyToManyRelationship;
    }

    /**
     * Get the reference that can be used to retrieve option pairs.  How we retrieve
     * this will vary for one-to-many vs many-to-many contexts.  In the case of
     * many-to-many fields, we grab it from the relationship definition rather than
     * the table metadata.
     *
     * @return array
     */
    protected function getOptionPairsReference()
    {
        return $this->manyToManyRelationship->getOptionPairsReference();
    }

    /**
     * Override \Dewdrop\Db\Field's isTypeManytomany() method to always return
     * true for many-to-many fields.
     *
     * @return boolean
     */
    protected function isTypeManytomany()
    {
        return true;
    }
}
