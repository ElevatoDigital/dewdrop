<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields;

class BootstrapColumnsModal extends AbstractHelper
{
    public function direct(Fields $availableFields, Fields $visibleFields, $actionUrl, $id = null)
    {
        return $this->partial(
            'bootstrap-columns-modal.phtml',
            array(
                'id'        => ($id ?: 'adjust-columns-modal'),
                'actionUrl' => $actionUrl,
                'visible'   => $visibleFields->getVisibleFields(),
                'available' => $availableFields->getVisibleFields()
            )
        );
    }
}
