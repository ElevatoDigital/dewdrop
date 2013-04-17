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
 * Create a checkbox list that enables a user to select multiple options
 * and sends an array value representing the selected options.
 */
class WpCheckboxList extends AbstractHelper
{
    /**
     * Render the checkbox list.
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
     * Throw an exception if a field object is passed to this helper.
     *
     * We will likely have field support for this view helper once an
     * abstraction is in place for managing many-to-many relationships.
     *
     * @param Field $field
     * @return string
     */
    protected function directField(Field $field)
    {
        throw new Exception('Passing a field object to WpCheckboxList is not currently supported.');
    }

    /**
     * Explicitly set the basic arguments for this helper and then render the
     * input.
     *
     * @param string $name
     * @param array $options
     * @param array $value
     * @return string
     */
    protected function directExplicit($name, array $options, $value)
    {
        return $this->directArray(
            array(
                'name'    => $name,
                'options' => $options,
                'value'   => $value
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

        if (null === $value) {
            $value = array();
        }

        return $this->partial(
            'wp-checkbox-list.phtml',
            array(
                'name'    => $name,
                'options' => $options,
                'value'   => $value,
                'classes' => $classes
            )
        );
    }

    /**
     * Prepare the options array for the directArray() method, checking that
     * required options are set, ensuring "classes" is an array and adding
     * "classes" to the options array, if they it is not present already.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'options', 'value'))
            ->ensurePresent($options, array('classes'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
