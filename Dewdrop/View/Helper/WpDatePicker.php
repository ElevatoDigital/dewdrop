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
 * Render the WordPress date picker.  This helper will automatically
 * enqueue the required CSS and JavaScript and render a text input to
 * store and transmit the selected value.
 */
class WpDatePicker extends AbstractHelper
{
    /**
     * Render a date picker using the method appropriate to the
     * supplied arguments.
     *
     * @return string
     */
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    /**
     * Use the supplied field to generate the date picker.
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
     * Render the date picker with explicitly passed arguments.
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
     * Use the supplied key-value options array to render the date
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

        if (null === $dateFormat) {
	    $dateFormat = 'yy-mm-dd';
        }

	wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('jquery-ui-css');
        wp_enqueue_script('wp-date-picker');
	wp_enqueue_script('jquery-ui-datepicker');

        $this->view->inlineScript(
            'wp-date-picker.js',
            array(
                'dateFormat'   => $dateFormat,
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
            ->ensurePresent($options, array('id', 'dateFormat'));

        return $options;
    }
}
