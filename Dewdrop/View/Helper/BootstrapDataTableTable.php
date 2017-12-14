<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;
use Dewdrop\Fields\Helper\SelectSort;

/**
 * Render a table that will be used with the DataTable library.
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
     * Get the indexes of columns that are sorted and not sorted.
     *
     * @return array
     */
    public function getColumnSortability()
    {
        return [
            'sortable'    => $this->sortableColumnIndexes,
            'notSortable' => $this->notSortableColumnIndexes
        ];
    }

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
}
