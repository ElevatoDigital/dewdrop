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

        // Ensure primary key field is instantiated so that it is linked by row editor
        $this->component->getFields()->add($this->component->getListing()->getPrimaryKey())
            ->setEditable(false);

        $this->rowEditor->link();

        $this->isNew = $this->rowEditor->isNew();

        $this->invalidSubmission = false;

        $this->checkPermissions();
    }

    protected function checkPermissions()
    {
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

                $this->rowEditor->save();

                if (!$this->request->isAjax()) {
                    $responseHelper->redirectToAdminPage('index');
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'result' => 'success',
                        'id'     => $this->component->getListing()->getPrimaryKey()->getValue()
                    ]);
                    exit;
                }
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
        $this->view->groupingFilter    = $this->component->getFieldGroupsFilter();
        $this->view->invalidSubmission = $this->invalidSubmission;
    }
}
