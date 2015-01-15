<?php

namespace Dewdrop\View\Helper;

class InputTimestamp extends BootstrapInputText
{
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
