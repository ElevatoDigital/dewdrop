<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields\Listing;
use Dewdrop\Notification\Gateway as NotificationGateway;

class Notifications extends PageAbstract
{
    public function render()
    {
        $gateway = new NotificationGateway($this->component->getDb());
        $select  = $gateway->selectByComponent($this->component->getFullyQualifiedName());

        $fields = $gateway->buildFields(
            $this->component->url('notification-edit'),
            $this->component->getFields()
        );

        $this->view->fields         = $fields;
        $this->view->listing        = new Listing($select);
        $this->view->component      = $this->component;
        $this->view->componentModel = $this->component->getPrimaryModel();
    }
}
