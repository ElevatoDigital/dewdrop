<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Admin\Component\Silex\CrudAbstract;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\CsvCell;

/**
 * Produces a CSV export
 */
class CsvExport extends AbstractHelper
{
    /**
     * @param CrudAbstract $component
     * @return string
     */
    public function render(CrudAbstract $component)
    {
        // Don't render the layout
        $component->setShouldRenderLayout(false);

        // Start output buffering
        ob_start();

        // Send headers
        $this->sendHeaders();

        // Get the visible component fields
        $fields = $component->getFields()->getVisibleFields();

        $csvCellRenderer = $this->view->csvCellRenderer();

        // Render header
        $csvRows[] = $this->renderHeader($fields, $csvCellRenderer);

        // Render content
        $csvRows = array_merge(
            $csvRows,
            $this->renderContent($fields, $component->getListing()->fetchData($fields), $csvCellRenderer)
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

        // Return output
        return $output;
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

    /**
     * Set response headers for a CSV download
     *
     * @return void
     */
    protected function sendHeaders()
    {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=export.csv");
    }
}
