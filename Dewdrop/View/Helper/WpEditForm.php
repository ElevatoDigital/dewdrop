<?php

namespace Dewdrop\View\Helper;

class WpEditForm extends AbstractHelper
{
    public function open($title, $method = 'POST', $action = null)
    {
        return $this->partial(
            'wp-edit-form-open.phtml',
            array(
                'title'  => $title,
                'method' => $method,
                'action' => ($action ?: $_SERVER['REQUEST_URI'])
            )
        );
    }

    public function close($buttonTitle = 'Save Changes')
    {
        return $this->partial(
            'wp-edit-form-close.phtml',
            array(
                'buttonTitle' => $buttonTitle
            )
        );
    }
}
