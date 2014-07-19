<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\FieldInterface;
use Zend\InputFilter\InputFilter as ZfInputFilter;

/**
 * This helper can provide Zend\InputFilter\Inputs for a supplied field.
 * In the case of DB objects, the Input automatically generated for the field,
 * using validators and filters appropriate for its data type and constraints,
 * will be used.  For custom fields, you just need to return a
 * \Zend\InputFilter\Input object for your field.  This is most commonly used
 * along with \Dewdrop\Fields\RowEditor.  If you are adding a custom field and
 * make it editable, you'll almost always need to provide a callback for this
 * helper so that it knows how to validate and filter that custom field.
 */
class InputFilter extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'inputfilter';

    /**
     * The \Zend\InputFilter\InputFilter onto which all the Input objects will
     * be added.
     *
     * @param ZfInputFilter $inputFilter
     */
    public function __construct(ZfInputFilter $inputFilter = null)
    {
        $this->inputFilter = ($inputFilter ?: new ZfInputFilter());
    }

    /**
     * Get the \Zend\InputFilter\InputFilter object this helper is managing.
     *
     * @return ZfInputFilter
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Add the \Zend\InputFilter\Input object for the given field.
     *
     * @param FieldInterface $field
     * @return $this
     */
    public function addInput(FieldInterface $field)
    {
        $callback = $this->getFieldAssignment($field);
        $input    = call_user_func($callback);

        $this->inputFilter->add($input);

        return $this;
    }

    /**
     * For DB fields, we can automatically return an Input.
     *
     * @param FieldInterface $field
     * @return bool|callable|mixed
     */
    public function detectCallableForField(FieldInterface $field)
    {
        if (!$field instanceof DbField) {
            return false;
        }

        return function ($helper) use ($field) {
            return $field->getInputFilter();
        };
    }
}
