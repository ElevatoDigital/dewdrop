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
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;
use Dewdrop\Fields\Listing;
use Dewdrop\View\Helper\BootstrapDataTableTable;
use Dewdrop\View\View;
use Dewdrop\View\Helper\DataTables as DataTablesHelper;

class DataTables extends HandlerAbstract
{
    /**
     * Get the sorted fields from the request.
     * Sample Request values:
     *
     * page:PublicationPages
     * route:index
     * format:datatables
     * draw:2
     * columns[0][data]:0
     * columns[0][name]:publication_pages-list_name // field's query string id
     * columns[0][searchable]:true
     * columns[0][orderable]:true
     * columns[0][search][value]:
     * columns[0][search][regex]:false
     * columns[1][data]:1
     * columns[1][name]:publication_pages-datetime_created // field's query string id
     * columns[1][searchable]:true
     * columns[1][orderable]:true
     * columns[1][search][value]:
     * columns[1][search][regex]:false
     * order[0][column]:0
     * order[0][dir]:asc
     * order[1][column]:1
     * order[1][dir]:desc
     * start:0
     * length:-1
     * search[value]:
     * search[regex]:false
     *
     * @param string $prefix
     * @return array
     */
    public function getSortFromRequest($prefix = '')
    {
        $sorts = [];
        $dirs  = [];

        $orders = $this->request->getQuery('order', []);
        foreach ($orders as $order) {
            $columns = $this->request->getQuery('columns', []);
            $fieldId = $columns[ $order['column'] ]['name'];

            $sorts[] = $fieldId;
            $dirs[]  = $order['dir'];
        }

        return [
            'sorts' => $sorts,
            'dirs'  => $dirs
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
        // AJAX requests
        if ('datatables' === $this->request->getQuery('format')) {
            // DataTables provides us with 'start'
            $start = (int) $this->request->getQuery('start', $pageSize);
            return ($start / $pageSize) + 1;
        }
        // Non-ajax
        else {
            return parent::getPageFromRequest($prefix, $pageSize);
        }
    }

    public function getData(Listing $listing, CrudAbstract $component, Fields $fields)
    {
        return []; // Always show an empty table, DataTables populates it via AJAX
    }

    /**
     * Get the table helper for this listing handler.
     *
     * @param View $view
     * @return BootstrapDataTableTable
     */
    public function getTableHelper(View $view)
    {
        return $view->bootstrapDataTableTable();
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
        return $view->bootstrapDataTableTable($fields, $data, $renderer, $sorter);
    }

    /**
     * The DataTables JavaScript library handles this so we don't want to render anything.
     *
     * @param View $view
     * @param int $rowCount
     * @param int $pageSize
     * @param int $page
     * @param string $title
     * @return string
     */
    public function renderPagination(View $view, $rowCount, $pageSize, $page, $title)
    {
        return '';
    }

    /**
     * @param View $view
     * @param Fields $fields
     * @param SelectSort $sorter
     * @param SelectPaginate $paginator
     * @param string $paginationTitle
     * @return string
     */
    public function renderFooter($view, $fields, $sorter, $paginator, $paginationTitle)
    {
        $out          = parent::renderFooter($view, $fields, $sorter, $paginator, $paginationTitle);
        $tableHelper  = $this->getTableHelper($view);
        $tableOptions = $this->getDataTableOptionsJson($tableHelper, $fields, $sorter, $paginator, $view->datatables(), $paginationTitle);

        $out .= <<<HTML
         <script type="application/json" id="datatables-options">
            {$tableOptions}
        </script>
HTML;

        $view->headLink()->appendStylesheet($view->bowerUrl('datatables.net-bs/css/dataTables.bootstrap.min.css'));

        return $out;
    }

    /**
     * Get options that will be used to initialize DataTables.
     * API for some of the options used: https://datatables.net/reference/option/
     *
     * @param BootstrapDataTableTable $tableHelper
     * @param Fields $fields
     * @param SelectSort $selectSort
     * @param SelectPaginate $paginator
     * @param DataTablesHelper $datatables
     * @param string $title Primary models title that is already made singular or plural.
     * @return string Sanitized JSON
     */
    private function getDataTableOptionsJson(BootstrapDataTableTable $tableHelper, Fields $fields, SelectSort $selectSort, SelectPaginate $paginator, DataTablesHelper $datatables, $title)
    {
        $sortableColumns       = $tableHelper->getColumnSortability();

        $options               = new \stdClass();
        $options->processing   = true;
        $options->serverSide   = true;
        $options->pageLength   = $paginator->getPageSize();
        $options->lengthMenu   = $datatables->getLengthMenuOptions();
        $options->searching    = false;
        $options->columnDefs   = [
            [
                'targets'    => $sortableColumns['sortable'],
                'orderable'  => true,
                'serverSide' => true
            ],
            [
                'targets'    => $sortableColumns['notSortable'],
                'orderable'  => false,
                'serverSide' => true
            ],
        ];
        $options->language     = [
            'info'         => '_TOTAL_ '.$title,
            'infoEmpty'    => '0 '.$title,
            'infoFiltered' => ' - filtered from _MAX_ '.$title,
            'lengthMenu'   => 'Show _MENU_ '.$title
        ];
        $options->columns      = [];
        $options->order        = [];

        $defaultSorting = $selectSort->getDefaultSorting($fields->getVisibleFields());
        $columnIndex    = 0;

        foreach ($fields->getVisibleFields() as $field) {
            $options->columns[] = [
                'name' => $field->getQueryStringId(),
                'data' => $field->getQueryStringId()
            ];

            $sorts = (array) $this->request->getQuery('sort');
            $dirs  = (array) $this->request->getQuery('dir');

            if (!empty($sorts)) {
                if (in_array($field->getQueryStringId(), $sorts)) {
                    $sortIndex = array_search($field->getQueryStringId(), $sorts);
                    $dir       = ('desc' === strtolower($dirs[$sortIndex])) ? 'desc' : 'asc';

                    $options->order[$sortIndex] = [$columnIndex, $dir];
                }
            } else {
                if ($field->isSortable() && $defaultSorting['field'] === $field) {
                    // Sadly no way to use the query string id, have to use index.
                    $options->order[] = [$columnIndex, strtolower($selectSort->getDefaultDirection())];
                }
            }

            $columnIndex++;
        }

        ksort($options->order);

        return json_encode($options);
    }
}
