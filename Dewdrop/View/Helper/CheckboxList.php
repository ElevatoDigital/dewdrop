<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\ManyToMany\Field;

/**
 * Create a checkbox list that enables a user to select multiple options
 * and sends an array value representing the selected options.
 */
class CheckboxList extends AbstractHelper
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
     * Use a ManyToMany field to render the checkbox list.
     *
     * @param Field $field
     * @param array $options
     * @return string
     */
    protected function directField(Field $field, array $options = array())
    {
        $fieldDefaults = array(
            'name'    => $field->getControlName(),
            'id'      => $field->getHtmlId(),
            'value'   => $field->getValue(),
            'options' => $field->getOptionPairs()->fetch()
        );

        return $this->directArray($fieldDefaults + $options);
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

        $classes[] = 'checkbox-list';

        return $this->partial(
            'checkbox-list.phtml',
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
    protected function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'options', 'value'))
            ->ensurePresent($options, array('classes'))
            ->ensureArray($options, array('classes'));

        return $options;
    }
}
