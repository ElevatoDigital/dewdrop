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
use Dewdrop\Exception;

/**
 * Render a basic HTML <select> element using the supplied options.
 */
class WpSelect extends AbstractHelper
{
    /**
     * Render the <select>.
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
     * Because we don't yet have an OptionPairs API added to Dewdrop\Db\Field,
     * we don't support passing a field directly to this view helper to generate
     * the <select>.
     *
     * @param Field $field
     * @throws \Dewdrop\Exception
     */
    public function directField(Field $field)
    {
        throw new Exception(
            'Passing a field to WpSelect is not currently supported because '
            . 'there is no OptionPairs API.'
        );
    }

    /**
     * Specify the basic options available for this view helper explicitly, as
     * basic PHP args.
     *
     * @param string $name The name attribute for the <select>.
     * @param array $options The key-value pairs representing the select options.
     * @param mixed $value The selected value.
     * @return string
     */
    public function directExplicit($name, array $options, $value)
    {
        return $this->directArray(
            array(
                'name'    => $name,
                'value'   => $value,
                'options' => $options
            )
        );
    }

    /**
     * Render the <select> using an array of name-value options.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (null === $id) {
            $id = $name;
        }

        $value = (string) $value;

        return $this->partial(
            'wp-select.phtml',
            array(
                'name'    => $name,
                'id'      => $id,
                'value'   => $value,
                'options' => $options
            )
        );
    }

    /**
     * Prepare the options array for the directArray() method, checking that
     * required options are set.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value', 'options'))
            ->ensurePresent($options, array('id'))
            ->ensureArray($options, array('options'));

        return $options;
    }
}
