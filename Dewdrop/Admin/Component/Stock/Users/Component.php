<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Listing;
use Dewdrop\Fields\RowEditor;
use Dewdrop\Pimple;
use Dewdrop\View\View;
use Zend\InputFilter\Input;
use Zend\Validator\Callback;
use Zend\Validator\StringLength;

/**
 * Admin user management component.
 *
 * This component provides user management facilities within the Dewdrop admin environment for Silex.
 *
 * Following is an example of how to register the component within your application's index.php facade script:
 *
 * <pre>
 * $zend = realpath(__DIR__ . '/../zend');
 *
 * if (file_exists($zend)) {
 *     define('PROJECT_ROOT', $zend);
 * } else {
 *     define('PROJECT_ROOT', realpath(__DIR__ . '/../'));
 * }
 *
 * require_once PROJECT_ROOT . '/vendor/autoload.php';
 *
 * $silex = \Dewdrop\Pimple::getInstance();
 *
 * $silex['auth']->init();
 *
 * $silex['admin']->registerComponentsInPath();
 *
 * $silex['admin']->registerComponent(new \Dewdrop\Admin\Component\Stock\Users\Component());
 *
 * $silex->run();
 * </pre>
 *
 * If you wish to extend this class, you can write your own \Admin\Users\Component class in /admin/users/Component.php,
 * and because the Dewdrop admin environment already scans the /admin directory for components, you do not need to
 * register your custom component in your application's index.php facade script as shown in the above example.
 */
class Component extends ComponentAbstract implements CrudInterface
{
    /**
     * Field groups filter
     *
     * @var GroupsFilter
     */
    protected $fieldGroupsFilter;

    /**
     * Fields collection
     *
     * @var Fields
     */
    protected $fields;

    /**
     * Fields listing
     *
     * @var Listing
     */
    protected $listing;

    /**
     * Row editor
     *
     * @var RowEditor
     */
    protected $rowEditor;

    /**
     * Fields visibility filter
     *
     * @var VisibilityFilter
     */
    protected $visibilityFilter;

    /**
     * Initializations
     *
     * @return void
     */
    public function init()
    {
        $this->setTitle('Users');

        $this->addPageFactory(new PageFactory($this));

        $this->getPermissions()
            ->register('change-password', 'Allow users to change their own password')
            ->set('change-password', true);
    }

    /**
     * Get the primary model that is used by this component.  This model will
     * be used to provide page and button titles.  By default, its primary key
     * will also be used to filter the listing when needed (e.g. when viewing
     * a single item rather than the full listing).
     *
     * @return \Dewdrop\Auth\Db\UsersTableGateway
     */
    public function getPrimaryModel()
    {
        return Pimple::getResource('users-gateway');
    }

    /**
     * Get the \Dewdrop\Fields\RowEditor object that will assist with the
     * editing of items in this component.
     *
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
     * Get a \Dewdrop\Fields\Listing object that allows the component to
     * retrieve records for viewing.  The Listing handles applying user sorts
     * and filters.
     *
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
     * Get the \Dewdrop\Fields object that defines what fields are available to
     * this component, what capabilities that each have, and how they should
     * interact with various \Dewdrop\Fields\Helper objects.
     *
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
                    )
                ->add($model->field('security_level_id'));
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
            $this->visibilityFilter = new VisibilityFilter($this->getFullyQualifiedName(), $this->getDb());
            $this->visibilityFilter->setDefaultFields($this->getFields()->getVisibleFields());
        }

        return $this->visibilityFilter;
    }

    /**
     * Set visibility filter
     *
     * @param VisibilityFilter $visibilityFilter
     * @return Component
     */
    public function setVisibilityFilter(VisibilityFilter $visibilityFilter)
    {
        $this->visibilityFilter = $visibilityFilter;

        return $this;
    }

    /**
     * Add password and confirmation fields to the given fields collection
     *
     * @param Fields $fields
     * @return Component
     */
    protected function addPasswordFields(Fields $fields)
    {
        static $passwordFieldName = 'password',
            $confirmFieldName  = 'confirm_password',
            $minimumLength     = 6;

        $request = $this->getRequest();

        $fields->add($passwordFieldName)
            ->assignHelperCallback(
                'EditControl.Control',
                function ($helper, View $view) use ($passwordFieldName, $request) {
                    return $view->inputText(
                        [
                            'name'  => $passwordFieldName,
                            'type'  => 'password',
                            'value' => $request->getPost($passwordFieldName),
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
                        ->attach(new StringLength(['min' => $minimumLength]));
                    return $input;
                }
            )
            ->setLabel('Password')
            ->setEditable(true);

        $this->fields->add('confirm_password')
            ->assignHelperCallback(
                'EditControl.Control',
                function ($helper, View $view) use ($confirmFieldName, $request) {
                    return $view->inputText(
                        [
                            'name'  => $confirmFieldName,
                            'type'  => 'password',
                            'value' => $request->getPost($confirmFieldName),
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
                        ->attach($passwordsMatchValidator);
                    return $input;
                }
            )
            ->setLabel('Confirm Password')
            ->setEditable(true);

        return $this;
    }
}
