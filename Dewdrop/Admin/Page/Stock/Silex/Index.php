<?php

namespace Dewdrop\Admin\Page\Stock\Silex;

use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Bootstrap;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Listing\Export\Csv as CsvExport;

class Index extends PageAbstract
{
    public function render()
    {
        $fields  = $this->component->getFields();
        $listing = $this->component->getListing();
        $filter  = $this->component->getVisibilityFilter();

        $this->view->singularTitle    = $this->component->getPrimaryModel()->getSingularTitle();
        $this->view->pluralTitle      = $this->component->getPrimaryModel()->getPluralTitle();
        $this->view->listing          = $listing;
        $this->view->visibilityFilter = $filter;
        $this->view->debug            = Bootstrap::getResource('debug');
    }
}
