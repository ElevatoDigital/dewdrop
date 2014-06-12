<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;

class Edit extends PageAbstract
{
    private $rowEditor;

    private $isNew;

    private $model;

    public function init()
    {
        $this->rowEditor = $this->component->getRowEditor();
        $this->model     = $this->component->getPrimaryModel();

        $this->rowEditor->link();

        $this->isNew = $this->rowEditor->isNew();

        $this->invalidSubmission = false;

        if ($this->isNew) {
            $this->component->getPermissions()->haltIfNotAllowed('create');
        } else {
            $this->component->getPermissions()->haltIfNotAllowed('edit');
        }
    }

    public function process($responseHelper)
    {
        if ($this->request->isPost()) {
            $this->invalidSubmission = (!$this->rowEditor->isValid($this->request->getPost()));

            if (!$this->invalidSubmission) {
                $title = strtolower($this->model->getSingularTitle());

                if ($this->isNew) {
                    $responseHelper->setSuccessMessage("Successfully saved new {$title}");
                } else {
                    $responseHelper->setSuccessMessage("Successfully saved changes to {$title}");
                }

                $responseHelper
                    ->run('save', array($this->rowEditor, 'save'))
                    ->redirectToAdminPage('index');
            }
        }
    }

    public function render()
    {
        $this->view->component         = $this->component;
        $this->view->isNew             = $this->isNew;
        $this->view->fields            = $this->component->getFields();
        $this->view->model             = $this->model;
        $this->view->rowEditor         = $this->rowEditor;
        $this->view->invalidSubmission = $this->invalidSubmission;
    }
}
