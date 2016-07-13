<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Eav\Field as EavField;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Exception;

/**
 * This class detects the appropriate view helper for editing a
 * \Dewdrop\Db\Field object.  It will look at the field's type to determine
 * a reasonable default helper, but you can also call the customizeField()
 * method to override the default for a specific field.
 */
class EditHelperDetector
{
    /**
     * An array of custom helper assignments.  The keys in the array are the
     * field's control name and the values are the name of the view helper
     * that should be used.
     *
     * @var array
     */
    private $customHelpers = array();

    /**
     * Assign a custom view helper for the provided field.  If set, the custom
     * helper will be used instead of the default that would otherwise be used.
     * You can supply the $field parameter as either a \Dewdrop\Db\Field object
     * or a string representing a field's control name.
     *
     * @param mixed $field
     * @param string $helperName
     * @return \Dewdrop\Fields\EditHelperDetector
     */
    public function customizeField($field, $helperName)
    {
        if ($field instanceof FieldInterface) {
            $field = $field->getControlName();
        }

        $this->customHelpers[$field] = $helperName;

        return $this;
    }

    /**
     * Detect which view helper should be used to edit the supplied
     * \Dewdrop\Db\Field.  THis is basic logic used to determine a suitable
     * helper:
     *
     * <ol>
     *     <li>
     *         If a custom helper was assigned by calling customizeField(),
     *         use that.
     *     </li>
     *     <li>
     *         If it is an EAV field, use whatever helper is assigned in the
     *         EAV definition.
     *     </li>
     *     <li>
     *         Otherwise, look at the field's type to determine which helper
     *         would be appropriate.
     *     </li>
     *     <li>
     *         If a suitable helper cannot be determined, throw an exception.
     *     </li>
     * </ol>
     *
     * @throws \Dewdrop\Exception
     * @param Field $field
     * @return string
     */
    public function detect(FieldInterface $field)
    {
        if (array_key_exists($field->getControlName(), $this->customHelpers)) {
            return $this->customHelpers[$field->getControlName()];
        } elseif ($field instanceof EavField) {
            return $field->getEditHelperName();
        } elseif ($field->isType('boolean', 'boolean')) {
            return 'inputCheckbox';
        } elseif ($field->isType('manytomany')) {
            return 'checkboxList';
        } elseif ($field->isType('reference')) {
            return 'select';
        } elseif ($field->isType('clob')) {
            return 'textarea';
        } elseif ($field->isType('text', 'integer', 'float')) {
            return 'inputText';
        } elseif ($field->isType('date')) {
            return 'inputDate';
        } elseif ($field->isType('timestamp')) {
            return 'inputTimestamp';
        }

        throw new Exception(
            'Fields\EditHelperDetector: Could not find a suitable view helper for field '
            . $field->getControlName() . '.'
        );
    }
}
