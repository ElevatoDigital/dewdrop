<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\SortableListingInterface;
use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Bootstrap;
use Dewdrop\Pimple;

class Index extends PageAbstract
{
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('view-listing');

        $fields  = $this->component->getFields();
        $listing = $this->component->getListing();
        $filter  = $this->component->getVisibilityFilter();

        $this->view->component        = $this->component;
        $this->view->permissions      = $this->component->getPermissions();
        $this->view->singularTitle    = $this->component->getPrimaryModel()->getSingularTitle();
        $this->view->pluralTitle      = $this->component->getPrimaryModel()->getPluralTitle();
        $this->view->listing          = $listing;
        $this->view->visibilityFilter = $filter;
        $this->view->groupingFilter   = $this->component->getFieldGroupsFilter();
        $this->view->fields           = $fields;
        $this->view->debug            = Pimple::getResource('debug');
        $this->view->isSortable       = ($this->component instanceof SortableListingInterface);

        $this->view->assign('page', $this);
    }
}
