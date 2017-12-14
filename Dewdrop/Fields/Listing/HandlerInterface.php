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
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectPaginate;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\Listing;
use Dewdrop\View\View;
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;

/**
 * Interface HandlerInterface
 *
 * @package Dewdrop\Fields\Listing
 */
interface HandlerInterface
{
    /**
     * Get the sorted fields from the request.
     *
     * @param string $prefix
     * @return array
     */
    public function getSortFromRequest($prefix);

    /**
     * Get the current page from the request.
     *
     * @param string $prefix
     * @param int $pageSize
     * @return int
     */
    public function getPageFromRequest($prefix, $pageSize);

    public function getData(Listing $listing, CrudAbstract $component, Fields $fields);

    /**
     * Get the table helper for this listing handler.
     *
     * @param View $view
     * @return mixed
     */
    public function getTableHelper(View $view);

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
    public function renderTable(View $view, Fields $fields, array $data, TableCellHelper $renderer = null, SelectSort $sorter = null);

    /**
     * Render pagination.
     *
     * @param View $view
     * @param integer $rowCount The total number of records available.
     * @param integer $pageSize The number of records to show on each page.
     * @param integer $page The currently selected page.
     * @param string $title The title to use in the record count.
     * @return string
     */
    public function renderPagination(View $view, $rowCount, $pageSize, $page, $title);

    /**
     * Render some HTML to go below the Listings Table and Pagination.
     * This is also used to add additional javascript/css to the page.
     *
     * @param View $view
     * @param Fields $fields
     * @param SelectSort $sorter
     * @param SelectPaginate $paginator
     * @param string $paginationTitle
     * @return string
     */
    public function renderFooter($view, $fields, $sorter, $paginator, $paginationTitle);
}
