<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Field;
use Dewdrop\Exception;
use Zend\InputFilter\InputFilter;

/**
 * Use the \Dewdrop\Db\Field API to manage the editing of values.
 *
 * Adding fields to this object simplifies a couple aspects of the common
 * add/edit workflow in a web application:
 *
 * <ol>
 *     <li>
 *         It automatically attaches the added fields to the input filter
 *         for filtering and validation.  The InputFilter will be injected
 *         into the constructor so that it can be integrated into some other
 *         context like an EditAbstract sub-class.
 *     </li>
 *     <li>
 *         When calling setValues(), this fields collection will automatically
 *         only set values on fields that you've explicitly added to the
 *         collection.  For example, a malicious user adding a field you did
 *         not intend to allow them to edit to their POST data would be
 *         thwarted by this API.  In other words, this API is intended as a
 *         way to prevent mass assignment (http://en.wikipedia.org/wiki/Mass_assignment_vulnerability).
 *         Think of adding a field to this object as passing a signed
 *         permission slip to the user saying they are allowed to edit that
 *         field on that particular row.
 *     </li>
 *     <li>
 *         When calling setValues(), this object will take care of odd quirks in
 *         input processing.  For example, when a checkbox is unchecked, it is
 *         excluded completely from the POST data.  This object will find boolean
 *         fields and if they are not in POST at all, set their values to false
 *         instead of just skipping over them while setting values for other
 *         fields whose control names _do_ appear in the supplied values hash.
 *     </li>
 * </ol>
 */
class Edit
{
    /**
     * The collection of fields added to this object
     *
     * @var array
     */
    private $fields = array();

    /**
     * Object used to filter and validate user input
     *
     * @var \Zend\InputFilter\InputFilter
     */
    private $inputFilter;

    /**
     * Store reference to supplied InputFilter so that fields can be added
     * to it at the same time they're added to this object.
     *
     * @param InputFilter $inputFilter
     */
    public function __construct(InputFilter $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * Add a field, optionally changing its control name to disambiguate it
     * from other fields with the same control name on this page.
     *
     * @param Field $field
     * @param string $groupName
     * @return \Dewdrop\Fields\Edit
     */
    public function add(Field $field, $groupName = null)
    {
        if (null === $groupName) {
            $this->fields[$field->getControlName()] = $field;
        } else {
            $fieldIndex = $groupName . ':' . $field->getName();
            $field->setControlName($fieldIndex);
            $this->fields[$fieldIndex] = $field;
        }

        $this->inputFilter->add($field->getInputFilter());

        return $this;
    }

    /**
     * Get the field matching the supplied control name.
     *
     * @throws \Dewdrop\Exception
     * @param string $controlName
     * @return \Dewdrop\Db\Field
     */
    public function get($controlName)
    {
        if (!$this->has($controlName)) {
            throw new Exception("Unknown field \"{$controlName}\" requested");
        }

        return $this->fields[$controlName];
    }

    /**
     * Check to see if this object has a reference to the field with the
     * provided control name.
     *
     * @param string $controlName
     * @return boolean
     */
    public function has($controlName)
    {
        return array_key_exists($controlName, $this->fields);
    }

    /**
     * Set values for any fields managed by this object with a control name
     * matching a key of the supplied $values array.
     *
     * This method is also responsible for transforming the input when
     * necessary for it to be useful for the database.  For example, when
     * unchecked, HTML inputs of type "checkbox" will not be present in
     * POST at all.  In that case, this method will detect that the POST
     * key is absent and set the assocaited field's value to zero.
     *
     * @param array $values
     * @return \Dewdrop\Fields\Edit
     */
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if ($this->has($key)) {
                $field = $this->get($key);

                // For some odd reason wp_editor() adds slashes by quotes.  Breaks
                // many things.  This works around that problem.
                if ($field->isType('text')) {
                    $value = stripslashes($value);
                }

                $field->setValue($value);
            }
        }

        // When not checked, checkboxes are excluded from POST in full.
        // This loop works around that quirk.
        foreach ($this->fields as $field) {
            if ($field->isType('tinyint') && !array_key_exists($field->getControlName(), $values)) {
                $field->setValue(0);
            }
        }

        return $this;
    }
}
