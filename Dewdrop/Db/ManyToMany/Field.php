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
use Dewdrop\Fields\OptionPairs;
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
     * @param Relationship
     * @return \Dewdrop\Db\ManyToMany\Field
     */
    public function setManyToManyRelationship(Relationship $manyToManyRelationship)
    {
        $this->manyToManyRelationship = $manyToManyRelationship;

        return $this;
    }

    /**
     * When retrieving option pairs for a many-to-many relationship, we don't look
     * for the reference on the source table, like with a one-to-many relationship.
     * Instead, we look for the matching reference on the cross-reference table.
     *
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function getOptionPairs()
    {
        if (null === $this->optionPairs) {
            $this->optionPairs = new OptionPairs($this->table->getAdapter());

            $ref = $this->manyToManyRelationship->getOptionPairsReference();

            if ($ref) {
                $this->optionPairs->setOptions(
                    array(
                        'tableName'   => $ref['table'],
                        'valueColumn' => $ref['column']
                    )
                );
            }
        }

        return $this->optionPairs;
    }

    /**
     * In the case of a ManyToMany field, we don't add any validators based on
     * the data type and other metadata tied to a physical database column.
     * The only input filter interaction we do is setting the allowEmpty property
     * depending upon whether this field is marked as required.
     *
     * @param Input $inputFilter
     * @return void
     */
    protected function addFiltersAndValidatorsUsingMetadata(Input $inputFilter)
    {
        if ($this->isRequired()) {
            $inputFilter->setAllowEmpty(false);
        } else {
            $inputFilter->setAllowEmpty(true);
        }
    }
}
