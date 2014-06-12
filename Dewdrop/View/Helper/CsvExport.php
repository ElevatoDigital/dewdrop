<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\CsvCell;

/**
 * Produces a CSV export
 */
class CsvExport extends AbstractHelper
{
    public function direct()
    {
        $args = func_get_args();

        if (0 === count($args)) {
            return $this;
        }

        if (!isset($args[0]) || !$args[0] instanceof Fields ||
            !isset($args[1]) || !is_array($args[1])
        ) {
            throw new Exception('CsvExport accepts either no arguments or a CrudInterface component');
        }

        if (!isset($args[2])) {
            $csvCellRenderer = $this->view->csvCellRenderer();
        } elseif ($args[2] instanceof CsvCell) {
            $csvCellRenderer = $args[2];
        } else {
            throw new Exception('Third argument should be null or a CsvCell helper');
        }

        $fields = $args[0];
        $data   = $args[1];
        $output = $this->render($fields, $data, $csvCellRenderer);

        $this->sendHeaders('export.csv');

        return $output;
    }

    /**
     * @param CrudInterface $component
     * @return string
     */
    public function render(Fields $fields, array $data, CsvCell $csvCellRenderer)
    {
        // Start output buffering
        ob_start();

        // Get the visible component fields
        $fields = $fields->getVisibleFields();

        // Render header
        $csvRows[] = $this->renderHeader($fields, $csvCellRenderer);

        // Render content
        $csvRows = array_merge(
            $csvRows,
            $this->renderContent($fields, $data, $csvCellRenderer)
        );

        // Output CSV data
        $outputHandle = fopen('php://output', 'w');
        foreach ($csvRows as $csvRow) {
            fputcsv($outputHandle, $csvRow);
        }
        fclose($outputHandle);

        // Get output buffer contents
        $output = ob_get_contents();

        // Erase output buffer and turn off
        ob_end_clean();

        return $output;
    }

    /**
     * Set response headers for a CSV download
     *
     * @return void
     */
    public function sendHeaders($filename)
    {
        if (!preg_match('/\.csv$/', $filename)) {
            $filename .= '.csv';
        }

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename={$filename}");
    }

    /**
     * @param Fields $fields
     * @param array $rows
     * @param CsvCell $csvCell
     * @return array
     */
    protected function renderContent(Fields $fields, array $rows, CsvCell $csvCell)
    {
        $outRows  = [];
        $rowIndex = 0;

        foreach ($rows as $row) {
            $outRow      = [];
            $columnIndex = 0;
            foreach ($fields as $field) {
                $outRow[] = $csvCell->getContentRenderer()->render($field, $row, $rowIndex, $columnIndex);
                $columnIndex += 1;
            }

            $outRows[] = $outRow;
            $rowIndex += 1;
        }

        return $outRows;
    }

    /**
     * @param Fields $fields
     * @param CsvCell $csvCell
     * @return array
     */
    protected function renderHeader(Fields $fields, CsvCell $csvCell)
    {
        $header = [];

        foreach ($fields as $field) {
            $header[] = $csvCell->getHeaderRenderer()->render($field);
        }

        return $header;
    }
}
