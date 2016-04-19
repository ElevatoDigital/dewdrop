<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\BulkActionProcessorInterface;
use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Component\SortableListingInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Bootstrap;
use Dewdrop\Pimple;
use Dewdrop\Session;

/**
 * Render the primary listing for a component.  This page is more complex
 * than others in the stock CRUD pages because it provides some navigation for
 * the remainder of the component and also provides support for some
 * supplementary interface a CRUD component cam implement:
 *
 * 1) SortableListingInterface: Makes the rows in a listing's table sortable.
 *
 * 2) BulkActionProcessInterface: Enables checkboxes on the listing rows to
 *    allow selection of records and application of actions to them in bulk.
 */
class Index extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * If a bulk action fails to process (e.g. returns an input validation
     * issue), this message will be populate and sent to the view.
     *
     * @var string
     */
    protected $bulkActionFailureMessage = '';

    /**
     * Session storage for remembering query params for redirects.
     *
     * @var Session
     */
    private $session;

    /**
     * The URL to use for the create button.
     *
     * @var string
     */
    private $createUrl;

    /**
     * Override the default URL used on the create button.
     *
     * @param string $createUrl
     * @return $this
     */
    public function setCreateUrl($createUrl)
    {
        $this->createUrl = $createUrl;

        return $this;
    }

    /**
     * Get the URL that should be used for the create button.  By default,
     * this uses the stock edit page class.
     *
     * @return string
     */
    public function getCreateUrl()
    {
        if (!$this->createUrl) {
            $this->createUrl = $this->getView()->adminUrl('edit');
        }

        return $this->createUrl;
    }

    /**
     * Ensure the user is allowed to view the listing in this component.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('view-listing');

        $this->session = new Session();
        $this->session->set($this->component->getListingQueryParamsSessionName(), $this->request->getQuery());

        if ($this->component instanceof SortableListingInterface) {
            $fields    = $this->component->getFields();
            $sortField = $this->component->getSortField();

            if (!$fields->has($sortField)) {
                $fields->add($sortField);
            }

            /* @var $sorter \Dewdrop\Fields\Helper\SelectSort */
            $sorter = $this->component->getListing()->getSelectModifierByName('SelectSort');
            $sorter->setDefaultField($sortField);
        }
    }

    /**
     * If our component is a BulkActionProcessorInterface implementer, then
     * process those here, handling the result and associated message.
     *
     * @param ResponseHelper $responseHelper
     */
    public function process(ResponseHelper $responseHelper)
    {
        if ($this->component instanceof BulkActionProcessorInterface) {
            $result = $this->component->getBulkActions()->process();

            if ($result) {
                if (!$result->isSuccess()) {
                    $this->bulkActionFailureMessage = $result->getMessage();
                } else {
                    $index  = $this->component->getListingQueryParamsSessionName();
                    $params = (isset($this->session[$index]) ? $this->session[$index] : []);

                    $responseHelper
                        ->setSuccessMessage($result->getMessage())
                        ->redirectToAdminPage('Index', $params);
                }
            }
        }
    }

    /**
     * Pass a whole log of stuff into the view.
     */
    public function render()
    {
        $fields  = $this->component->getFields();
        $listing = $this->component->getListing();
        $filter  = $this->component->getVisibilityFilter();

        $this->view->assign([
            'component'        => $this->component,
            'permissions'      => $this->component->getPermissions(),
            'singularTitle'    => $this->component->getPrimaryModel()->getSingularTitle(),
            'pluralTitle'      => $this->component->getPrimaryModel()->getPluralTitle(),
            'listing'          => $listing,
            'visibilityFilter' => $filter,
            'groupingFilter'   => $this->component->getFieldGroupsFilter(),
            'fields'           => $fields,
            'debug'            => Pimple::getResource('debug'),
            'isSortable'       => ($this->component instanceof SortableListingInterface),
            'page'             => $this,
            'createUrl'        => $this->getCreateUrl()
        ]);

        if ($this->component instanceof BulkActionProcessorInterface) {
            $this->view->assign([
                'bulkActions'              => $this->component->getBulkActions(),
                'bulkActionFailureMessage' => $this->bulkActionFailureMessage
            ]);
        }

        return $this->renderView();
    }
}
