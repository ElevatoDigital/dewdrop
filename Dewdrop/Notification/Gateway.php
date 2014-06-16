<?php

namespace Dewdrop\Notification;

use Dewdrop\Db\Expr;
use Dewdrop\Db\Table;
use Dewdrop\Fields;
use Zend\Escaper\Escaper;
use Zend\InputFilter\Input;

class Gateway extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_notification_subscriptions');

        $this->customizeField(
            'dewdrop_notification_frequency_id',
            function ($field) {
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
                    function ($helper, array $rowData) use ($editUrl) {
                        return $helper->getEscaper()->escapeHtml(
                            $this->renderRecipients($rowData['dewdrop_notification_subscription_id'])
                        );
                    }
                )
                ->setEditable(true)
                ->assignHelperCallback(
                    'EditControl.Control',
                    function ($helper, $view) {
                        return $view->inputText(
                            'recipients',
                            $this->renderRecipients($view->getRequest()->getQuery('dewdrop_notification_subscription_id')),
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
            ->add($this->field('when_added'))
                ->assignHelperCallback(
                    'TableCell.Content',
                    function ($helper, array $rowData) {
                        return $this->renderBooleanTableCell($rowData['when_added']);
                    }
                )
            ->add($this->field('when_edited'))
                ->assignHelperCallback(
                    'TableCell.Content',
                    function ($helper, array $rowData) {
                        return $this->renderBooleanTableCell($rowData['when_edited']);
                    }
                )
            ->add('fields')
                ->setLabel('Which fields would you like to include in the notification emails?')
                ->setEditable(true)
                ->assignHelperCallback(
                    'EditControl.Control',
                    function ($helper, $view) use ($componentFields) {
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

    private function renderBooleanTableCell($value)
    {
        return sprintf(
            '<span class="text-%s glyphicon glyphicon-%s"></span>',
            ($value ? 'success' : 'danger'),
            ($value ? 'ok' : 'remove')
        );
    }

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
