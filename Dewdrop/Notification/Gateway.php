<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Notification;

use Dewdrop\Db\Field;
use Dewdrop\Db\Table;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\TableCell\Content as TableCellHelper;
use Dewdrop\View\View;
use Zend\InputFilter\Input;

/**
 * A table gateway for interacting with dewdrop_notification_subscriptions data
 * in the DB.
 */
class Gateway extends Table
{
    /**
     * Basic configuration and field customization.
     */
    public function init()
    {
        $this->setTableName('dewdrop_notification_subscriptions');

        $this->customizeField(
            'dewdrop_notification_frequency_id',
            function (Field $field) {
                $field->setLabel('Frequency');
            }
        );
    }

    /**
     * This method exists so that subscriptions cannot be pulled up with just
     * an ID.  This ensures that manipulating the ID in the query string doesn't
     * allow a user to bypass permissions checks on components for which they
     * are not allowed to manage subscriptions.
     *
     * @param int $id
     * @param string $component
     * @return \Dewdrop\Db\Row
     */
    public function findByIdAndComponent($id, $component)
    {
        $select = $this->select();

        $select
            ->from('dewdrop_notification_subscriptions')
            ->where('component = ?', $component)
            ->where('dewdrop_notification_subscription_id = ?', $id);

        return $this->fetchRow($select);
    }

    /**
     * Select the notification subscriptions for the specified component.
     *
     * @param $fullyQualifiedComponentName
     * @return \Dewdrop\Db\Select
     */
    public function selectByComponent($fullyQualifiedComponentName)
    {
        $select = $this->select();

        $select
            ->from(
                array('s' => 'dewdrop_notification_subscriptions')
            )
            ->join(
                array('f' => 'dewdrop_notification_frequencies'),
                'f.dewdrop_notification_frequency_id = s.dewdrop_notification_frequency_id',
                array('dewdrop_notification_frequency' => 'name')
            )
            ->where('component = ?', $fullyQualifiedComponentName)
            ->order('s.date_created');

        return $select;
    }

    /**
     * Build a Fields object that can be used for displaying or editing
     * subscriptions.
     *
     * @todo Refactor this into a separate class.
     *
     * @param string $editUrl
     * @param Fields $componentFields
     * @return Fields
     */
    public function buildFields($editUrl, Fields $componentFields)
    {
        $fields = new Fields();

        $fields
            ->add('recipients')
                ->setLabel('Recipients')
                ->setNote('Enter one or more email addresses separated by commas.')
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCellHelper $helper, array $rowData) use ($editUrl) {
                        return $helper->getView()->escapeHtml(
                            $this->renderRecipients($rowData['dewdrop_notification_subscription_id'])
                        );
                    }
                )
                ->setEditable(true)
                ->assignHelperCallback(
                    'EditControl.Control',
                    function ($helper, View $view) {
                        return $view->inputText(
                            'recipients',
                            $this->renderRecipients(
                                $view->getRequest()->getQuery('dewdrop_notification_subscription_id')
                            ),
                            'form-control'
                        );
                    }
                )
                ->assignHelperCallback(
                    'InputFilter',
                    function ($helper) {
                        $input = new Input('recipients');
                        $input->setAllowEmpty(false);
                        return $input;
                    }
                )
            ->add($this->field('dewdrop_notification_frequency_id'))
            ->add('fields')
                ->setLabel('Which fields would you like to include in the notification emails?')
                ->setEditable(true)
                ->assignHelperCallback(
                    'EditControl.Control',
                    function ($helper, View $view) use ($componentFields) {
                        $options = array();

                        foreach ($componentFields->getVisibleFields() as $id => $field) {
                            $options[$id] = $field->getLabel();
                        }

                        return $view->checkboxList(
                            'fields',
                            $options,
                            $this->getSelectedFields(
                                $view->getRequest()->getQuery('dewdrop_notification_subscription_id'),
                                $options
                            )
                        );
                    }
                )
                ->assignHelperCallback(
                    'InputFilter',
                    function ($helper) {
                        $input = new Input('fields');
                        $input->setAllowEmpty(false);
                        return $input;
                    }
                );

        return $fields;
    }

    /**
     * Render a comma-separated list of recipients for a subscription.
     *
     * @param $subscriptionId
     * @return string
     */
    private function renderRecipients($subscriptionId)
    {
        if (!$subscriptionId) {
            return '';
        }

        $select = $this->select();

        $select
            ->from('dewdrop_notification_subscription_recipients', array('email_address'))
            ->where('dewdrop_notification_subscription_id = ?', $subscriptionId)
            ->order('email_address');

        return implode(', ', $this->getAdapter()->fetchCol($select));
    }

    /**
     * Get the fields that have been selected for a subscription.
     *
     * @todo Can now use or ManyToMany stuff instead of this.
     *
     * @param $id
     * @param array $options
     * @return array
     */
    private function getSelectedFields($id, array $options)
    {
        if (!$id) {
            return array_splice(array_keys($options), 0, 5);
        }

        $select = $this->select();

        $select
            ->from('dewdrop_notification_subscription_fields', array('field_id'))
            ->where('dewdrop_notification_subscription_id = ?', $id);

        $selected = $this->getAdapter()->fetchCol($select);

        if (0 === count($selected)) {
            return array_splice(array_keys($options), 0, 5);
        }

        return $selected;
    }
}
