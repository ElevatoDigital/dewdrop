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
     * DataTable options for page length.
     * https://datatables.net/reference/option/lengthMenu
     *
     * @var array
     */
    private $lengthMenuOptions = [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'ALL']];

    /**
     * Get lengthMenu value for DataTables.
     * https://datatables.net/reference/option/lengthMenu
     *
     * @return array
     */
    public function getLengthMenuOptions()
    {
        return $this->lengthMenuOptions;
    }

    /**
     * @param array $options An array compatible with DataTables lengthMenu option
     * https://datatables.net/reference/option/lengthMenu
     * @return $this
     */
    public function setLengthMenuOptions(array $options)
    {
        $this->lengthMenuOptions = $options;

        return $this;
    }

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
     * @return array
     */
    public function getData(Fields $fields, array $data, TableCellHelper $renderer)
    {
        $rowIndex = 0;
        $out      = [];
        $view     = $renderer->getContentRenderer()->getView();


        foreach ($data as $dataRow) {
            $columnIndex = 0;
            $row         = [];

            if ($view->bulkActions) {
                $fields->prepend($view->bulkActionCheckboxField($view->bulkActions, $renderer));
            }

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
