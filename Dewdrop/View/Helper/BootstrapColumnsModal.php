<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;

class BootstrapColumnsModal extends AbstractHelper
{
    public function direct(VisibilityFilter $filter, $actionUrl, $id = null)
    {
        return $this->partial(
            'bootstrap-columns-modal.phtml',
            array(
                'id'        => ($id ?: 'adjust-columns-modal'),
                'actionUrl' => $actionUrl,
                'visible'   => $filter->getVisibleFields(),
                'available' => $filter->getAllAvailableFields()
            )
        );
    }
}
