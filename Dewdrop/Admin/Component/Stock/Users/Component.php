<?php

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\RowEditor;
use Dewdrop\Pimple;
use Model\Users;
use Zend\InputFilter\Input;
use Zend\Validator\Callback;
use Zend\Validator\StringLength;

class Component extends ComponentAbstract implements CrudInterface
{
    /**
     * @var GroupsFilter
     */
    protected $fieldGroupsFilter;

    /**
     * @var Fields
     */
    protected $fields;

    /**
     * @var Listing
     */
    protected $listing;

    /**
     * @var RowEditor
     */
    protected $rowEditor;

    /**
     * @var VisibilityFilter
     */
    protected $visibilityFilter;

    /**
     * @return void
     */
    public function init()
    {
        $this->setTitle('Users');

        $this->getPermissions()
            ->register('change-password', 'Allow users to change their own password')
            ->set('change-password', true);
    }

    /**
     * @return \Dewdrop\Auth\Db\UsersTableGateway
     */
    public function getPrimaryModel()
    {
        return Pimple::getResource('users-gateway');
    }

    /**
     * @return RowEditor
     */
    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $fields          = $this->getFields();
            $this->rowEditor = new RowEditor($fields, $this->getRequest());
            $this->rowEditor
                ->linkByQueryString('users', 'user_id')
                ->link();
            if ($this->rowEditor->isNew()) {
                $this->addPasswordFields($fields);
            }
        }

        return $this->rowEditor;
    }

    /**
     * @return Listing
     */
    public function getListing()
    {
        if (!$this->listing) {
            $this->listing = new Listing(
                $this->getPrimaryModel()->selectAdminListing(),
                $this->getPrimaryModel()->field('user_id')
            );
        }

        return $this->listing;
    }

    /**
     * @return Fields
     */
    public function getFields()
    {
        if (!$this->fields) {
            $this->fields = new Fields();

            $model = $this->getPrimaryModel();

            $this->fields
                ->add($model->field('first_name'))
                ->add($model->field('last_name'))
                ->add($model->field('username'))
                ->add($model->field('email_address'))
                    ->assignHelperCallback(
                        'TableCell.Content',
                        function (Fields\Helper\TableCell\Content $helper, array $rowData) {
                            $escaper = $helper->getEscaper();
                            return sprintf(
                                '<a href="mailto:%s">%s</a>',
                                $escaper->escapeHtmlAttr($rowData['email_address']),
                                $escaper->escapeHtml($rowData['email_address'])
                            );
                        }
                    );
        }

        return $this->fields;
    }

    /**
     * Get a \Dewdrop\Fields\Filter\Groups object to allow the user to sort
     * and group their fields.
     *
     * @return GroupsFilter
     */
    public function getFieldGroupsFilter()
    {
        if (!$this->fieldGroupsFilter) {
            $this->fieldGroupsFilter = new GroupsFilter(
                $this->getFullyQualifiedName(),
                $this->getDb()
            );
        }

        return $this->fieldGroupsFilter;
    }

    /**
     * Get a \Dewdrop\Fields\Filter\Visibility object that allows the user
     * to select which fields should be visible on listings.
     *
     * @return VisibilityFilter
     */
    public function getVisibilityFilter()
    {
        if (!$this->visibilityFilter) {
            $this->visibilityFilter = new VisibilityFilter(
                $this->getFullyQualifiedName(),
                $this->getDb()
            );
        }

        return $this->visibilityFilter;
    }

    /**
     * @param Fields $fields
     * @return Component
     */
    protected function addPasswordFields(Fields $fields)
    {
        static
            $passwordFieldName = 'password',
            $confirmFieldName  = 'confirm_password',
            $minimumLength     = 6;

        $request = $this->getRequest();

        $fields->add($passwordFieldName)
            ->assignHelperCallback(
                'EditControl.Control',
                function ($helper, $view) use ($request) {
                    return $view->inputText(
                        [
                            'name'      => 'password',
                            'type'      => 'password',
                            'value'     => $request->getPost('password'),
                            'autofocus' => 'autofocus',
                        ]
                    );
                }
            )
            ->assignHelperCallback(
                'InputFilter',
                function () use ($passwordFieldName, $minimumLength) {
                    $input = new Input($passwordFieldName);
                    $input
                        ->setRequired(true)
                        ->getValidatorChain()
                        ->addValidator(new StringLength(['min' => $minimumLength]));
                    return $input;
                }
            )
            ->setLabel('Password')
            ->setEditable(true);

        $this->fields->add('confirm_password')
            ->assignHelperCallback(
                'EditControl.Control',
                function ($helper, $view) use ($request) {
                    return $view->inputText(
                        [
                            'name'  => 'confirm_password',
                            'type'  => 'password',
                            'value' => $request->getPost('confirm_password'),
                        ]
                    );
                }
            )
            ->assignHelperCallback(
                'InputFilter',
                function () use ($request, $passwordFieldName, $confirmFieldName) {
                    $input                   = new Input($confirmFieldName);
                    $passwordsMatchValidator = new Callback([
                        'callback' => function ($value) use ($request, $passwordFieldName) {
                            return $value === $request->getPost($passwordFieldName);
                        }
                    ]);
                    $passwordsMatchValidator->setMessage('Passwords do not match.');
                    $input
                        ->setRequired(true)
                        ->getValidatorChain()
                        ->addValidator($passwordsMatchValidator);
                    return $input;
                }
            )
            ->setLabel('Confirm Password')
            ->setEditable(true);

        return $this;
    }
}
