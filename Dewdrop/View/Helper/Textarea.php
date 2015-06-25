<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use \Dewdrop\Db\Field;
use \Dewdrop\Exception;

/**
 * Render a standard HTML textarea.  This helper can optionally leverage a
 * \Dewdrop\Db\Field object to set it's options.
 *
 * Example usage:
 *
 * <pre>
 * echo $this->textarea($this->fields->get('animals:long_description'));
 * </pre>
 */
class Textarea extends AbstractHelper
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

        return $this->directArray($fieldDefaults + $options);
    }

    /**
     * Explicitly set the basic arguments for this helper and then render the
     * input.
     *
     * @param string $name
     * @param boolean $value
     * @param string $class
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

        if (null === $id) {
            $id = $name;
        }

        if (null === $rows) {
            $rows = 6;
        }

        if (null === $cols) {
            $cols = 52;
        }

        return $this->partial(
            'textarea.phtml',
            array(
                'name'      => $name,
                'id'        => $id,
                'value'     => $value,
                'classes'   => $classes,
                'rows'      => $rows,
                'cols'      => $cols,
                'autofocus' => $autofocus
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
            ->ensurePresent($options, array('classes', 'id', 'rows', 'cols', 'autofocus'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
