<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;

class SortFields extends PageAbstract
{
    private $filter;

    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('sort-fields');

        $this->filter = new GroupsFilter(
            $this->component->getFullyQualifiedName(),
            $this->component->getDb()
        );
    }

    public function process($responseHelper)
    {
        if ($this->request->isPost()) {
            $responseHelper->run(
                'save',
                function () {
                    $this->filter->save(
                        json_decode($this->request->getPost('sorted_fields'), true)
                    );
                }
            );

            $responseHelper
                ->setSuccessMessage("Successfully sorted and grouped {$this->component->getTitle()} fields")
                ->redirectToAdminPage('index');
        }
    }

    public function render()
    {
        $this->view->fieldGroups = $this->filter->getConfigForFields($this->component->getFields());
        $this->view->component   = $this->component;
        $this->view->fields      = $this->component->getFields();
    }
}
