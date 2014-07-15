<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Fields as FieldsSet;

/**
 * Custom fields, those not directly related to a database column or
 * relationship, generally use this class, unless you are implementing
 * FieldInterface yourself.
 */
class Field extends FieldAbstract
{
    /**
     * The ID used by this field.
     *
     * @var string
     */
    private $id;

    /**
     * The label to use when displaying the field.
     *
     * @var string
     */
    private $label;

    /**
     * The field value
     *
     * @var mixed
     */
    private $value;

    /**
     * Change the field's ID.
     *
     * @param string $id
     * @return Field
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Retrieve the field's ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the field's ID for use in HTML contexts.  This ID should be compatible
     * with CSS selectors, etc.  For custom fields, this is generally equivalent
     * to the ID itself.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getId();
    }

    /**
     * Get the field's ID for use in query strings.  This ID should be compatible
     * with URL query strings.  For custom fields, this is generally equivalent
     * to the ID itself.
     *
     * @return string
     */
    public function getQueryStringId()
    {
        return $this->getId();
    }

    /**
     * Set the label to be used for this field.  On custom fields, no inflection
     * or auto-detection is done for the label, so you'll need to call setLabel()
     * on essentially every custom field you write.
     *
     * @param string $label
     * @return Field
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label that should be used when displaying this field.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the value for this field.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value for this field.
     *
     * @param mixed $value
     * @return Field
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
