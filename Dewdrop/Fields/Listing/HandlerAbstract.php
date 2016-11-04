<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Listing;

use Dewdrop\Admin\Component\CrudAbstract;
use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectPaginate;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\Listing;
use Dewdrop\Pimple;
use Dewdrop\Request;
use Dewdrop\View\Helper\BootstrapTable;
use Dewdrop\View\View;
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;

class HandlerAbstract implements HandlerInterface
{
    /**
     * The HTTP request.
     *
     * @var Request
     */
    protected $request;

    /**
     * HandlerAbstract constructor.
     *
     * @param Request $request
     */
    public function __construct($request = null)
    {
        $this->request = ($request ?: Pimple::getResource('dewdrop-request'));
    }

    /**
     * Get the sorted fields from the request.
     *
     * @param string $prefix
     * @return array
     */
    public function getSortFromRequest($prefix = '')
    {
        return [
            'sorts' => (array) $this->request->getQuery($prefix.'sort'),
            'dirs'  => (array) $this->request->getQuery($prefix.'dir')
        ];
    }

    /**
     * Get the current page from the request.
     *
     * @param string $prefix
     * @param int $pageSize
     * @return int
     */
    public function getPageFromRequest($prefix, $pageSize)
    {
        return (int) $this->request->getQuery($prefix . 'listing-page', 1);
    }

    public function getData(Listing $listing, CrudAbstract $component, Fields $fields)
    {
        return $listing->fetchData($component->getFieldGroupsFilter()->apply($fields));
    }

    /**
     * Get the table helper for this listing handler.
     *
     * @param View $view
     * @return BootstrapTable
     */
    public function getTableHelper(View $view)
    {
        return $view->bootstrapTable();
    }

    /**
     * Render the table for the listing.
     *
     * @param View $view
     * @param Fields $fields
     * @param array $data
     * @param TableCellHelper|null $renderer
     * @param SelectSort|null $sorter
     * @return mixed
     */
    public function renderTable(View $view, Fields $fields, array $data, TableCellHelper $renderer = null, SelectSort $sorter = null)
    {
        return $view->bootstrapTable($fields, $data, $renderer, $sorter);
    }

    /**
     * @param View $view
     * @param int $rowCount
     * @param int $pageSize
     * @param int $page
     * @param string $title
     * @return string
     */
    public function renderPagination(View $view, $rowCount, $pageSize, $page, $title)
    {
        return $view->pagination($rowCount, $pageSize, $page, $title);
    }

    /**
     * Add javascript and css necessary for a listing.
     *
     * @param View $view
     * @param Fields $fields
     * @param SelectSort $sorter
     * @param SelectPaginate $paginator
     * @param string $paginationTitle
     * @return string
     */
    public function renderFooter($view, $fields, $sorter, $paginator, $paginationTitle)
    {
        $view->headLink()->appendStylesheet($view->bowerUrl('/dewdrop/www/css/table.css'));
        $view->headScript()->appendFile($view->bowerUrl('/dewdrop/www/js/listing-keyboard-shortcuts.js'));

        return '';
    }
}
