<?php

namespace Dewdrop\View\Helper;

use \Dewdrop\Db\Field;
use \Dewdrop\Exception;

/**
 * Render a text input node.  This helper can optionally leverage a
 * \Dewdrop\Db\Field object to set it's options.
 *
 * Example usage:
 *
 * <code>
 * echo $this->wpInputText($this->fields->get('animals:latin_name'));
 * </code>
 */
class WpInputText extends AbstractHelper
{
    /**
     * Render the input.
     *
     * This method will delegate to directField(), directExplicit(), or
     * directArray() depending upon the arguments that are passed to it.
     *
     * @return string
     */
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    /**
     * Use the supplied \Dewdrop\Db\Field object to set the helper's options
     * and then render the input.
     *
     * @param \Dewdrop\Db\Field
     * @return string
     */
    protected function directField(Field $field)
    {
        return $this->directArray(
            array(
                'name'  => $field->getControlName(),
                'id'    => $field->getControlName(),
                'value' => $field->getValue()
            )
        );
    }

    /**
     * Explicitly set the basic arguments for this helper and then render the
     * input.
     *
     * @param string $name
     * @param boolean $value
     * @param string $label
     * @return string
     */
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

    /**
     * Set the helper's options using an array of key-value pairs and then
     * render the input.
     *
     * @param array $options
     * @return string
     */
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

    /**
     * Prepare the options array for the directArray() method, checking that
     * required options are set, ensuring "classes" is an array and adding
     * "classes" and "id" to the options array, if they are not present
     * already.
     *
     * @return array
     */
    private function prepareOptionsArray($options)
    {
        $this
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('classes', 'id'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
