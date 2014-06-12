<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Notification\Gateway as NotificationGateway;

class NotificationEdit extends PageAbstract
{
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

        if (!$id) {
            $this->row = $gateway->createRow();
        } else {
            $this->row = $gateway->findByIdAndComponent($id, $this->component->getFullyQualifiedName());
        }

        foreach ($this->fields as $field) {
            if ($field instanceof DbField) {
                $field->setRow($this->row);
            }
        }
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
        $this->view->breadcrumbTitle = ($this->row->isNew() ? 'Add' : 'Edit');
    }
}
