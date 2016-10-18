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
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;


/**
 * Helper to facilitate use of the DataTables JavaScript library.
 * API for some of the options used: https://datatables.net/reference/option/
 */
class DataTables extends AbstractHelper
{

    /**
     * @param Fields $fields
     * @param array $data
     * @param TableCellHelper $renderer
     * @param int $totalRowCount
     * @return array
     */
    public function render(Fields $fields, array $data, TableCellHelper $renderer, $totalRowCount)
    {
        return [
            'recordsTotal'    => (int) $totalRowCount,
            'recordsFiltered' => (int) $totalRowCount,
            'data'            => $this->getData($fields, $data, $renderer)
        ];
    }

    /**
     * Get collection of rows, each row containing an array of the fields rendered content, indexed by the field id
     * @param Fields $fields
     * @param array $data
     * @param TableCellHelper $renderer
     * @return string
     */
    public function getData(Fields $fields, array $data, TableCellHelper $renderer)
    {
        $rowIndex = 0;

        $out = [];

        foreach ($data as $dataRow) {
            $columnIndex = 0;
            $row         = [];

            foreach ($fields as $field) {
                $row[$field->getQueryStringId()] = $renderer->getContentRenderer()->render($field, $dataRow, $rowIndex, $columnIndex);

                $columnIndex += 1;
            }

            $out[] = $row;

            $rowIndex += 1;
        }

        return $out;
    }
}
