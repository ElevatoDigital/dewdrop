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
use Dewdrop\Fields\Listing;
use Dewdrop\Notification\Gateway as NotificationGateway;

/**
 * This page is part of an experimental feature (currently disabled by default
 * in component Permissions.  It lists the notification subscriptions a user
 * has configured.  We're hoping to develop this feature so that users can be
 * notified when items in their components are created or updated.
 */
class Notifications extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Ensure the user is allowed to use the notifications feature on this
     * component.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('notifications');
    }

    /**
     * Setup notification fields and pass various dependencies to the View.
     */
    public function render()
    {
        $gateway = new NotificationGateway($this->component->getDb());
        $select  = $gateway->selectByComponent($this->component->getFullyQualifiedName());

        $fields = $gateway->buildFields(
            $this->component->url('notification-edit'),
            $this->component->getFields()
        );

        $this->view->assign([
            'fields'         => $fields,
            'listing'        => new Listing($select, $gateway->field('dewdrop_notification_subscription_id')),
            'component'      => $this->component,
            'componentModel' => $this->component->getPrimaryModel()
        ]);

        return $this->renderView();
    }
}
