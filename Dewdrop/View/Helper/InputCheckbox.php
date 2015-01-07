<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;

/**
 * Render a checkbox node.  This helper can optionally leverage a
 * \Dewdrop\Db\Field object to set its options.
 *
 * Example usage:
 *
 * <pre>
 * echo $this->wpInputCheckbox($this->fields->get('animals:is_mammals'));
 * </pre>
 */
class InputCheckbox extends AbstractHelper
{
    /**
     * Render the checkbox.
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
     * and then render the checkbox.
     *
     * @param Field $field
     * @return string
     */
    protected function directField(Field $field)
    {
        return $this->directArray(
            array(
                'name'  => $field->getControlName(),
                'id'    => $field->getHtmlId(),
                'value' => $field->getValue(),
                'label' => $field->getLabel()
            )
        );
    }

    /**
     * Explicitly set the basic arguments for this helper and then render the
     * checkbox.
     *
     * @param string $name
     * @param boolean $value
     * @param string $label
     * @return string
     */
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

    /**
     * Set the helper's options using an array of key-value pairs and then
     * render the checkbox.
     *
     * @param array $options
     * @return string
     */
    protected function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (null === $id) {
            $id = $name;
        }

        return $this->partial(
            'input-checkbox.phtml',
            array(
                'name'    => $name,
                'id'      => $id,
                'value'   => $value,
                'classes' => $classes,
                'label'   => $label
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
            ->checkRequired($options, array('name', 'value', 'label'))
            ->ensurePresent($options, array('classes', 'id'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
