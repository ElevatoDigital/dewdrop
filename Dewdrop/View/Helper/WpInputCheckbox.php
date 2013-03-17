<?php

namespace Dewdrop\View\Helper;

use \Dewdrop\Db\Field;
use \Dewdrop\Exception;

class WpInputCheckbox extends AbstractHelper
{
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    protected function directField(Field $field)
    {
        return $this->directArray(
            array(
                'name'  => $field->getControlName(),
                'id'    => $field->getControlName(),
                'value' => $field->getValue(),
                'label' => $field->getLabel()
            )
        );
    }

    protected function directExplicit($name, $value, $label)
    {
        return $this->directArray(
            array(
                'name'  => $name,
                'value' => $value,
                'label' => $label
            )
        );
    }

    protected function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (null === $id) {
            $id = $name;
        }

        return $this->partial(
            'wp-input-checkbox.phtml',
            array(
                'name'    => $name,
                'id'      => $id,
                'value'   => $value,
                'classes' => $classes,
                'label'   => $label
            )
        );
    }

    private function prepareOptionsArray($options)
    {
        $this
            ->checkRequired($options, array('name', 'value', 'label'))
            ->ensurePresent($options, array('classes', 'id'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
