<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields;
use Dewdrop\Pimple;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Callback;
use Zend\Validator\StringLength;

/**
 * Change password page
 */
class ChangePassword extends PageAbstract
{
    /**
     * Users table data gateway
     *
     * @var \Dewdrop\Auth\Db\UsersTableGateway
     */
    protected $model;

    /**
     * Fields collection
     *
     * @var Fields
     */
    protected $fields;

    /**
     * Input filter
     *
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     * Create any resources that need to be accessible both for processing
     * and rendering.
     *
     * @return void
     */
    public function init()
    {
        $this->model  = Pimple::getResource('users-gateway');
        $this->fields = new Fields();
        $this->row    = $this->model->find($this->request->getQuery('user_id'));

        $this->fields
            ->add('password')
                ->setLabel('Password')
                ->setEditable(true)
            ->add('confirm_password')
                ->setLabel('Confirm Password')
                ->setEditable(true);

        $this->inputFilter = new InputFilter();

        $password = new Input('password');
        $this->inputFilter->add($password);

        $password
            ->setRequired(true)
            ->getValidatorChain()
                ->attach(new StringLength(array('min' => 6)));

        $confirm = new Input('confirm_password');
        $this->inputFilter->add($confirm);

        $validator = new Callback(array(
            'callback' => function ($value) {
                return $value === $this->request->getPost('password');
            }
        ));

        $validator->setMessage('Passwords do not match.');

        $confirm
            ->setRequired(true)
            ->getValidatorChain()
                ->attach($validator);
    }

    /**
     * Perform any processing or data manipulation needed before render.
     *
     * A response helper object will be passed to this method to allow you to
     * easily add success messages or redirects.  This helper should be used
     * to handle these kinds of actions so that you can easily test your
     * page's code.
     *
     * @param \Dewdrop\Admin\ResponseHelper\Standard $responseHelper
     * @return \Dewdrop\Admin\ResponseHelper\Standard|null
     */
    public function process($responseHelper)
    {
        $isCurrentUser = ($this->row->get('user_id') === Pimple::getResource('user')->get('user_id'));

        if (!$this->component->getPermissions()->can('edit') && !$isCurrentUser) {
            return $responseHelper->redirectToUrl('/admin');
        }

        if ($this->request->isPost()) {
            $this->inputFilter->setData($this->request->getPost());

            if ($this->inputFilter->isValid()) {
                $this->row
                    ->hashPassword($this->request->getPost('password'))
                    ->save();

                if ($isCurrentUser) {
                    return $responseHelper->redirectToUrl('/admin');
                } else {
                    return $responseHelper->redirectToAdminPage('index');
                }
            }
        }
    }

    /**
     * Assign variables to your page's view and render the output.
     *
     * @return void
     */
    public function render()
    {
        $this->view->inputFilter = $this->inputFilter;
        $this->view->fields      = $this->fields;
        $this->view->component   = $this->component;
    }
}
