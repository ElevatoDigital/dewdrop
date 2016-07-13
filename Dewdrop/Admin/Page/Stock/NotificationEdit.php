<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields;
use Dewdrop\Fields\RowEditor;
use Dewdrop\Notification\Gateway as NotificationGateway;

/**
 * This page is part of an experimental feature (currently disabled by default
 * in component Permissions.  It allows creation of a notification subscription
 * for a component.  We're hoping to develop this feature so that users can be
 * notified when items in their components are created or updated.
 */
class NotificationEdit extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * A RowEditor that handles input validation/saving for the subscription.
     *
     * @var RowEditor
     */
    private $rowEditor;

    /**
     * Fields object form the notification model.
     *
     * @var Fields
     */
    private $fields;

    /**
     * Ensure the user has permissions to work with notifications in this component
     * and setup Fields and RowEditor objects.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('notifications');

        $gateway = new NotificationGateway($this->component->getDb());

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

    /**
     * Actually save the new notification subscription.
     *
     * @todo Make this do something.  Anything.
     * @param ResponseHelper $responseHelper
     */
    public function process(ResponseHelper $responseHelper)
    {
        if ($this->request->isPost()) {

        }
    }

    /**
     * Pass dependencies into the View.
     */
    public function render()
    {
        $this->view->assign([
            'component'       => $this->component,
            'componentModel'  => $this->component->getPrimaryModel(),
            'fields'          => $this->fields,
            'rowEditor'       => $this->rowEditor,
            'breadcrumbTitle' => ($this->rowEditor->isNew() ? 'Add' : 'Edit')
        ]);

        return $this->renderView();
    }
}
