<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\CrudAbstract;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Fields\Listing;

/**
 * Render a table that will be used with the DataTable library.
 * @deprecated You cannot have a <table> / HTML based endpoint for DataTables so only going to support JSON source.
 */
class BootstrapDataTableTable extends BootstrapTable
{
    /**
     * Collection of column indexes that are sortable.
     * @var array
     */
    protected $sortableColumnIndexes = [];

    /**
     * Collection of column indexes that are not sortable.
     * @var array
     */
    protected $notSortableColumnIndexes = [];

    /**
     * Render all the &lt;th&gt; cells that will be contained in the &lt;thead&gt;.
     *
     * @param Fields $fields
     * @param TableCellHelper $renderer
     * @param SelectSort $sorter
     * @return string
     */
    protected function renderHeadCells(Fields $fields, TableCellHelper $renderer, SelectSort $sorter = null)
    {
        $out            = '';
        $numericalIndex = 0;

        /* @var $field FieldInterface */
        foreach ($fields as $index => $field) {
            if ($field->isSortable()) {
                $pattern                          = '<th scope="col" class="sortable">%s</th>';
                $this->sortableColumnIndexes[]    = $numericalIndex;
            } else {
                $pattern                          = '<th scope="col" class="not-sortable">%s</th>';
                $this->notSortableColumnIndexes[] = $numericalIndex;
            }

            $content = $renderer->getHeaderRenderer()->render($field);
            $out    .= sprintf($pattern, $this->view->escapeHtml($content));

            $numericalIndex++;
        }
        return $out;
    }

    /**
     * This isn't used so make it return empty.
     *
     * @param string $content
     * @param string $queryStringId
     * @param string $direction
     * @param SelectSort $sorter
     * @return string
     */
    protected function renderSortLink($content, $queryStringId, $direction, SelectSort $sorter = null)
    {
        return '';
    }

    /**
     * Get options that will be used to initialize DataTables.
     * API for some of the options used: https://datatables.net/reference/option/
     *
     * @param Fields $fields
     * @param Listing $listing
     * @param DataTables $datatables
     * @param string $title Primary models title that is already made singular or plural.
     * @return string Sanitized JSON
     */
    public function getDataTableOptionsJson(Fields $fields, Listing $listing, DataTables $datatables, $title)
    {
        /** @var SelectSort $selectSort */
        $selectSort            = $listing->getSelectSortModifier();
        $options               = new \stdClass();
        $options->processing   = true;
        $options->serverSide   = true;
        $options->pageLength   = $listing->getSelectPaginateModifier()->getPageSize();
        $options->lengthMenu   = $datatables->getLengthMenuOptions();
        $options->searching    = false;
        $options->columnDefs   = [
            [
                'targets'    => $this->sortableColumnIndexes,
                'orderable'  => true,
                'serverSide' => true
            ],
            [
                'targets'    => $this->notSortableColumnIndexes,
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

        $index = 0;
        foreach ($fields->getVisibleFields() as $field) {
            $options->columns[] = [
                'name' => $field->getQueryStringId(),
                'data' => $field->getQueryStringId()
            ];
            if ($field->isSortable() && (null === $selectSort->getDefaultField() || $selectSort->getDefaultField() === $field)) {
                // Sadly no way to use the query string id, have to use index.
                $options->order[] = [$index, strtolower($selectSort->getDefaultDirection())];
            }

            $index++;
        }


        return json_encode($options);
    }

    public function getData(Listing $listing, CrudAbstract $component, Fields $fields)
    {
        return []; // Always show an empty table, DataTables populates it via AJAX
    }
}
