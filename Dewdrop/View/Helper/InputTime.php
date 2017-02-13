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
 * Renders a basic time input.
 */
class InputTime extends BootstrapInputText
{
    /**
     * Returns a basic time input.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        if (!isset($options['classes'])) {
            $options['classes'] = [];
        }

        $options['classes'][] = 'input-time';

        $options['type'] = 'time';

        if (isset($options['value']) && $options['value']) {
            $options['value'] = date('H:i:s', strtotime($options['value']));
        }

        return parent::directArray($options);
    }
}
