<?php

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields;
use Dewdrop\Pimple;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Callback;
use Zend\Validator\StringLength;

class ChangePassword extends PageAbstract
{
    protected $model;

    protected $fields;

    protected $inputFilter;

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
                ->addValidator(new StringLength(array('min' => 6)));

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
                ->addValidator($validator);
    }

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

    public function render()
    {
        $this->view->inputFilter = $this->inputFilter;
        $this->view->fields      = $this->fields;
        $this->view->component   = $this->component;
    }
}
