<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;

class Edit extends PageAbstract
{
    private $rowLinker;

    private $isNew;

    private $model;

    public function init()
    {
        $this->rowLinker = $this->component->getRowLinker();
        $this->model     = $this->component->getPrimaryModel();

        $this->rowLinker->apply();

        $this->isNew = $this->rowLinker->isNew();
    }

    public function process($responseHelper)
    {
        if ($this->request->isPost() &&
            $this->rowLinker->isValid($this->request->getPost())
        ) {
            $title = strtolower($this->model->getSingularTitle());

            if ($this->isNew) {
                $responseHelper->setSuccessMessage("Successfully saved new {$title}");
            } else {
                $responseHelper->setSuccessMessage("Successfully saved changes to {$title}");
            }

            $responseHelper
                ->run('save', array($this->rowLinker, 'save'))
                ->redirectToAdminPage('index');
        }
    }

    public function render()
    {
        $this->view->component = $this->component;
        $this->view->isNew     = $this->isNew;
        $this->view->fields    = $this->component->getFields();
        $this->view->model     = $this->model;
        $this->view->rowLinker = $this->rowLinker;
    }
}
