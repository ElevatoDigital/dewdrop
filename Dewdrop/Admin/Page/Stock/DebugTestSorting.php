<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Fields;
use Dewdrop\Fields\Test\ListingSort as ListingSortTest;
use ReflectionClass;

class DebugTestSorting extends PageAbstract
{
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $tester = new ListingSortTest(
            $this->component->getFields(),
            $this->component->getListing()
        );

        $reflection = new ReflectionClass($this->component);

        $this->view->namespace       = $reflection->getNamespaceName();
        $this->view->component       = $this->component;
        $this->view->results         = $tester->run();
        $this->view->displayFields   = new Fields();
        $this->view->componentFields = $this->component->getFields();
    }
}
