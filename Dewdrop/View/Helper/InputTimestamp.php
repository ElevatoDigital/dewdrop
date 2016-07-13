<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * A view helper to allow entry of date and time values.  The view helper
 * uses JavaScript to render two separate inputs (one with a date picker
 * and one with a time picker) that are then combined on the client-side
 * and passed back to the server via a hidden input.
 */
class InputTimestamp extends BootstrapInputText
{
    /**
     * Queue client-side dependencies and render the plain text input that
     * will be replaced with a hidden input and a set of date and time
     * pickers in JavaScript.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        $this->view->headScript()
            ->appendFile($this->view->bowerUrl('/dewdrop/www/js/datetime-picker.js'));

        $this->view->headLink()
            ->appendStylesheet($this->view->bowerUrl('/timepicker/jquery.timepicker.css'))
            ->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/datetime-picker.css'));

        if (!isset($options['classes'])) {
            $options['classes'] = [];
        }

        $options['classes'][] = 'input-timestamp';

        if (isset($options['value']) && $options['value']) {
            $options['value'] = date('m/d/Y g:iA', strtotime($options['value']));
        }

        return parent::directArray($options);
    }
}
