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
 * Render the WordPress color picker.  This helper will automatically
 * enqueue the required CSS and JavaScript and render a text input to
 * store and transmit the selected value.
 */
class WpColorPicker extends AbstractHelper
{
    /**
     * Render a color picker using the method appropriate to the
     * supplied arguments.
     *
     * @return string
     */
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    /**
     * Use the supplied field to generate the color picker.
     *
     * @param Field $field
     * @return string
     */
    public function directField(Field $field)
    {
        return $this->directArray(
            array(
                'name'  => $field->getControlName(),
                'id'    => $field->getHtmlId(),
                'value' => $field->getValue()
            )
        );
    }

    /**
     * Render the color picker with explicitly passed arguments.
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    public function directExplicit($name, $value)
    {
        return $this->directArray(
            array(
                'name'  => $name,
                'value' => $value
            )
        );
    }

    /**
     * Use the supplied key-value options array to render the color
     * picker.
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

        if (null === $defaultColor) {
            $defaultColor = '#ffffff';
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        $this->view->inlineScript(
            'wp-color-picker.js',
            array(
                'defaultColor' => $defaultColor,
                'palettes'     => $palettes,
                'id'           => $id
            )
        );

        return $this->view->wpInputText(
            array(
                'name'    => $name,
                'id'      => $id,
                'value'   => $value
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
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('id', 'defaultColor'))
            ->ensureArray($options, array('palettes'));

        return $options;
    }
}
