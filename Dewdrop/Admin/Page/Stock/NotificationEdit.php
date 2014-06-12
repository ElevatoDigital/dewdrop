<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\RowEditor;
use Dewdrop\Notification\Gateway as NotificationGateway;

class NotificationEdit extends PageAbstract
{
    private $rowEditor;

    private $fields;

    private $row;

    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('notifications');

        $gateway = new NotificationGateway($this->component->getDb());
        $id      = $this->request->getQuery('dewdrop_notification_subscription_id');

        $this->fields = $gateway->buildFields(
            $this->component->url('notification-edit'),
            $this->component->getFields()
        );

        $this->rowEditor = new RowEditor($this->fields, $this->request);

        $this->rowEditor->linkByQueryString(
            'dewdrop_notification_subscriptions',
            'dewdrop_notification_subscription_id'
        );

        $this->rowEditor->link();
    }

    public function process($responseHelper)
    {
        if ($this->request->isPost()) {

        }
    }

    public function render()
    {
        $this->view->component       = $this->component;
        $this->view->componentModel  = $this->component->getPrimaryModel();
        $this->view->fields          = $this->fields;
        $this->view->rowEditor       = $this->rowEditor;
        $this->view->breadcrumbTitle = ($this->rowEditor->isNew() ? 'Add' : 'Edit');
    }
}
