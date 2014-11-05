<?php

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\BulkActionProcessorInterface;
use Dewdrop\Admin\Component\SortableListingInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Admin\Page\PageAbstract;
use Dewdrop\Bootstrap;
use Dewdrop\Pimple;

class Index extends PageAbstract
{
    private $bulkActionFailureMessage = '';

    public function process(ResponseHelper $responseHelper)
    {
        if ($this->component instanceof BulkActionProcessorInterface) {
            $result = $this->component->getBulkActions()->process();

            if ($result) {
                if (!$result->isSuccess()) {
                    $this->bulkActionFailureMessage = $result->getMessage();
                } else {
                    $responseHelper
                        ->setSuccessMessage($result->getMessage())
                        ->redirectToAdminPage('Index');
                }
            }
        }
    }

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

        if ($this->component instanceof BulkActionProcessorInterface) {
            $this->view->bulkActions = $this->component->getBulkActions();

            $this->view->bulkActionFailureMessage = $this->bulkActionFailureMessage;
        }

        $this->view->assign('page', $this);
    }
}
