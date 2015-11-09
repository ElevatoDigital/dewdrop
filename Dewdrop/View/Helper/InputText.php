<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

/**
 * Render a text input node.  This helper can optionally leverage a
 * \Dewdrop\Db\Field object to set its options.
 *
 * Example usage:
 *
 * <pre>
 * echo $this->wpInputText($this->fields->get('animals:latin_name'));
 * </pre>
 */
class InputText extends AbstractHelper
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
     * @param Field $field
     * @param array $options
     * @return string
     */
    protected function directField(Field $field, array $options = array())
    {
        $fieldDefaults = array(
            'name'  => $field->getControlName(),
            'id'    => $field->getHtmlId(),
            'value' => $field->getValue()
        );

        return $this->directArray(array_merge($fieldDefaults, $options));
    }

    /**
     * Explicitly set the basic arguments for this helper and then render the
     * input.
     *
     * @param string $name
     * @param boolean $value
     * @param mixed $classes
     * @return string
     */
    protected function directExplicit($name, $value, $classes = null)
    {
        if (null !== $classes && !is_array($classes)) {
            $classes = array($classes);
        }

        return $this->directArray(
            array(
                'name'    => $name,
                'value'   => $value,
                'classes' => $classes
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

        if (!$type) {
            $type = 'text';
        }

        return $this->partial(
            'input-text.phtml',
            array(
                'name'        => $name,
                'id'          => $id,
                'value'       => $value,
                'classes'     => $classes,
                'type'        => $type,
                'autofocus'   => $autofocus,
                'placeholder' => $placeholder
            )
        );
    }

    /**
     * Prepare the options array for the directArray() method, checking that
     * required options are set, ensuring "classes" is an array and adding
     * "classes" and "id" to the options array, if they are not present
     * already.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('classes', 'id', 'autofocus', 'placeholder', 'type'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
