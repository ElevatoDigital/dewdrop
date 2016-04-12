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
use Dewdrop\Fields\Helper\CsvCell;
use Dewdrop\Fields\Listing;

/**
 * Generate a CSV export.
 *
 * @todo Look into effect on memory usage of using output buffering here.
 *
 * @todo Look into ways to improve handling of response headers.
 */
class CsvExport extends AbstractHelper
{
    /**
     * If called with no arguments, this method will return the instance of
     * CsvExport helper itself, so you can call other methods on it directly.
     * If called with arguments, however, it will go immediately to the render()
     * method.
     *
     * @return $this|string
     */
    public function direct()
    {
        return $this->delegateIfArgsProvided(func_get_args());
    }

    /**
     * If the direct() method is called with arguments, we validate them with
     * this method before calling render().
     *
     * @param Fields $fields
     * @param array $data
     * @param CsvCell $csvCellRenderer
     * @return string
     */
    protected function directWithArgs(Fields $fields, array $data, CsvCell $csvCellRenderer = null)
    {
        if (null === $csvCellRenderer) {
            $csvCellRenderer = $this->view->csvCellRenderer();
        }

        $this->sendHeaders('export.csv');

        return $this->render($fields, $data, $csvCellRenderer);
    }

    /**
     * Render the actual CSV data using the supplied Fields and data.
     *
     * @param Fields $fields
     * @param array $data
     * @param CsvCell $csvCellRenderer
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
     * @param Fields $fields
     * @param Listing $listing
     * @param CsvCell $csvCellRenderer
     * @return void
     */
    public function renderWithGenerator(Fields $fields, Listing $listing, CsvCell $csvCellRenderer)
    {
        // Output CSV data
        $outputHandle = fopen('php://output', 'w');

        // Get the visible component fields
        $fields = $fields->getVisibleFields();

        // Render header
        fputcsv($outputHandle, $this->renderHeader($fields, $csvCellRenderer));

        // Render content
        $rowIndex = 0;
        foreach ($listing->fetchDataWithGenerator($fields) as $row) {
            $csvRow      = [];
            $columnIndex = 0;
            foreach ($fields as $field) {
                $csvRow[] = $csvCellRenderer->getContentRenderer()->render($field, $row, $rowIndex, $columnIndex);
                $columnIndex++;
            }
            fputcsv($outputHandle, $csvRow);
            $rowIndex++;
        }

        // Close output handle
        fclose($outputHandle);
    }

    /**
     * Set response headers for a CSV download.
     *
     * @param string $filename
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
     * Render the rows of output in the body of the CSV.
     *
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
     * Render header labels for the CSV output.
     *
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
