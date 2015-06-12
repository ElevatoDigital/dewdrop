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
 * This helper uses a jQuery UI based date picker to manipulate
 * date values.  In most situations, you'll want to filter the
 * value coming back from the client to ensure it can be saved
 * back to the database, etc.
 */
class InputDate extends BootstrapInputText
{
    /**
     * Queue up client-side resources and render the basic text input.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        $this->view->headScript()
            ->appendFile($this->view->bowerUrl('/dewdrop/www/js/date-picker.js'));

        $this->view->headLink()
            ->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/datetime-picker.css'));

        if (!isset($options['classes'])) {
            $options['classes'] = [];
        }

        $options['classes'][] = 'input-date';

        if (isset($options['value']) && $options['value']) {
            $options['value'] = date('m/d/Y', strtotime($options['value']));
        }

        return parent::directArray($options);
    }
}
