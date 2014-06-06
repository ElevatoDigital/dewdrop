<?php

namespace Dewdrop\Fields\Listing\Export;

use Dewdrop\Fields\Listing;

class Csv
{
    private $filename = 'Data Export';

    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    public function render(Listing $listing)
    {
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=' . urlencode($this->filename) . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        $headers = array();

        foreach ($listing->getVisibleFields() as $column) {
            $headers[] = $column->renderHeader();
        }

        fputcsv($out, $headers);

        foreach ($listing->fetchData() as $row) {
            $csvFields = array();

            foreach ($listing->getVisibleFields() as $field) {
                $classes = array();

                $csvFields[] = $column->renderCell($row, $classes);
            }

            fputcsv($out, $csvFields);
        }

        fclose($out);
        exit;
    }
}
