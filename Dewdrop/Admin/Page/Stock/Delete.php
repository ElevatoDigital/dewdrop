<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;

class Delete extends PageAbstract
{
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('delete');
    }

    public function process()
    {
        if ($this->request->isPost()) {
            $rowEditor = $this->component->getRowEditor();

            $rowEditor->link();
            $rowEditor->delete();

            header('Content-Type: application/json');
            echo json_encode(['result' => 'success']);
            exit;
        }
    }

    public function render()
    {
        header('Content-Type: application/json');
        echo json_encode(['result' => 'error']);
        exit;
    }
}
