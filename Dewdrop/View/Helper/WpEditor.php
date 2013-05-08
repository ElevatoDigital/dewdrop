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
use Dewdrop\Filter\Stripslashes;

/**
 * Use the WordPress editor in a form.
 *
 * More information the wp_editor() function and the arguments it accepts in
 * its settings parameter is available at the following link.
 *
 * @link http://codex.wordpress.org/Function_Reference/wp_editor
 */
class WpEditor extends AbstractHelper
{
    /**
     * This function will delegate to either directField(), directExplicit(),
     * or directArray() depending upon the supplied arguments.
     *
     * @return string
     */
    public function direct()
    {
        return $this->delegateByArgs(func_get_args(), 'direct');
    }

    /**
     * Use a \Dewdrop\Db\Field object to set the editor's id and content.
     *
     * @param Field $field
     * @return string
     */
    public function directField(Field $field)
    {
        $field->getFilterChain()->attach(new StripSlashes());

        return $this->directArray(
            array(
                'name'  => $field->getControlName(),
                'value' => $field->getValue(),
                'id'    => $field->getHtmlId()
            )
        );
    }

    /**
     * Explicitly specify the editor's $name and $value.
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
     * Use an array of key-value pairs to set the editor's arguments.
     *
     * @param array $options
     */
    public function directArray(array $options)
    {
        extract($this->prepareOptionsArray($options));

        if (!isset($settings['textarea_name'])) {
            $settings['textarea_name'] = $name;
        }

        if (!$id) {
            $id = $name;
        }

        ob_start();

        wp_editor(
            $value,
            $id,
            $settings
        );

        return ob_get_clean();
    }

    /**
     * Ensure that the "name" and "value" options are set and the settings
     * option is present and an array.
     *
     * @param array $options
     * @return array
     */
    private function prepareOptionsArray(array $options)
    {
        $this
            ->checkRequired($options, array('name', 'value'))
            ->ensurePresent($options, array('id', 'settings'))
            ->ensureArray($options, array('settings'));

        return $options;
    }
}
