<?php

namespace Dewdrop\Admin\Component\Stock\Users;

use Dewdrop\Admin\Component\CrudAbstract;
use Dewdrop\Fields;
use Dewdrop\Fields\Listing;
use Dewdrop\Pimple;

class Component extends CrudAbstract
{
    protected $listing;

    protected $fields;

    public function init()
    {
        $this->setTitle('Users');
    }

    public function getPrimaryModel()
    {
        return Pimple::getResource('users-gateway');
    }

    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = parent::getRowEditor();
            $this->rowEditor->linkByQueryString('users', 'user_id');
        }

        return $this->rowEditor;
    }

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
                        function ($helper, array $rowData) {
                            return sprintf(
                                '<a href="mailto:%s">%s</a>',
                                $helper->getEscaper()->escapeHtmlAttr($rowData['email_address']),
                                $helper->getEscaper()->escapeHtml($rowData['email_address'])
                            );
                        }
                    )
                ->add($model->field('role'));
        }

        return $this->fields;
    }
}
