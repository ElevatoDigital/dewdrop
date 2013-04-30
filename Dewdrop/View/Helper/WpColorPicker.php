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
     * Use the supplied key-value options array to render the color
     * picker.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_script('wp-color-picker');

        $this->view->inlineScript(
            'wp-color-picker.js',
            array(
                'defaultColor' => $options['defaultColor'],
                'palettes'     => $options['palettes'],
                'id'           => $options['id']
            )
        );

        return $this->view->wpInputText(
            array(
                'name'    => $options['name'],
                'id'      => $options['id'],
                'value'   => $options['value']
            )
        );
    }
}
