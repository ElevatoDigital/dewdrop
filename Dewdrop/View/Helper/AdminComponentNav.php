<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\CrudInterface;

class AdminComponentNav extends AbstractHelper
{
    public function direct(CrudInterface $component)
    {
        return $this->partial(
            'admin-component-nav.phtml',
            array(
                'permissions'   => $component->getPermissions(),
                'singularTitle' => $component->getPrimaryModel()->getSingularTitle()
            )
        );
    }
}
