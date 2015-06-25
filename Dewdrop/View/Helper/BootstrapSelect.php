<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

/**
 * A simple Select wrapper that automatically includes the form-control
 * class needed for Boostrap styles to be applied.
 */
class BootstrapSelect extends Select
{
    /**
     * Augment whatever classes the user is supplying manually with
     * Boostrap's form-control class.
     *
     * @param array $options
     * @return string
     */
    public function directArray(array $options)
    {
        if (!isset($options['classes'])) {
            $options['classes'] = array();
        } elseif (!is_array($options['classes'])) {
            $options['classes'] = array($options['classes']);
        }

        $options['classes'][] = 'form-control';

        return parent::directArray($options);
    }
}
