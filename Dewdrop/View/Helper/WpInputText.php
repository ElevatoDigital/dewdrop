<?php

namespace Dewdrop\View\Helper;

use \Dewdrop\Db\Field;
use \Dewdrop\Exception;

class WpInputText extends AbstractHelper
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
                'value' => ($field->getValue() ?: '')
            )
        );
    }

    protected function directExplicit($name, $value, $class = null)
    {
        return $this->directArray(
            array(
                'name'    => $name,
                'value'   => $value,
                'classes' => ($class ? array($class) : null)
            )
        );
    }

    protected function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (0 === count($classes)) {
            $classes[] = 'regular-text';
        }

        if (null === $id) {
            $id = $name;
        }

        return $this->partial(
            'wp-input-text.phtml',
            array(
                'name'    => $name,
                'id'      => $id,
                'value'   => $value,
                'classes' => $classes
            )
        );
    }

    private function prepareOptionsArray($options)
    {
        $this
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('classes', 'id'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
