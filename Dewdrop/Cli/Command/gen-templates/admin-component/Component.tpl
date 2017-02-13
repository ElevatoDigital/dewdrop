<?php

namespace Admin\{{namespace}};

use Dewdrop\Admin\Component\CrudAbstract;
use Dewdrop\Fields;
use Dewdrop\Fields\Listing;
use Dewdrop\Admin\Component\ComponentAbstract;

class Component extends ComponentAbstract
{
    protected $model;

    protected $fields;

    public function init()
    {
        $this
            ->setTitle('{{title}}');
    }

    public function getPrimaryModel()
    {
        if (!$this->model) {
            $this->model = new {{namespace}}();
        }

        return $this->model;
    }

    public function getListing()
    {
        if (!$this->listing) {
            $this->listing = new Listing(
                $this->getPrimaryModel()->selectAdminListing(),
                $this->getPrimaryModel()->field('{{primaryKey}}')
            );
        }

        return $this->listing;
    }

    public function getFields()
    {
        if (!$this->fields) {
            $this->fields = new Fields();

            $model = $this->getPrimaryModel();
        }

        return $this->fields;
    }

    public function getRowEditor()
    {
        if (!$this->rowEditor) {
            $this->rowEditor = parent::getRowEditor();
            $this->rowEditor
                ->linkByQueryString('{{title}}', '{{primaryKey}}');
        }

        return $this->rowEditor;
    }
}
