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

class InputFilter extends HelperAbstract
{
    protected $name = 'inputfilter';

    public function __construct(ZfInputFilter $inputFilter = null)
    {
        $this->inputFilter = ($inputFilter ?: new ZfInputFilter());
    }

    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    public function addInput(FieldInterface $field)
    {
        $callback = $this->getFieldAssignment($field);
        $input    = call_user_func($callback);

        $this->inputFilter->add($input);

        return $this;
    }

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
