<?php

namespace Dewdrop\View\Helper;

class BootstrapSelect extends Select
{
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
