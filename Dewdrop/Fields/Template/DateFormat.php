<?php

namespace Dewdrop\Fields\Template;

use DateTimeImmutable;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\CsvCell\Content as CsvCell;
use Dewdrop\Fields\Helper\TableCell\Content as TableCell;

class DateFormat
{
    private $format;

    public function __construct($format)
    {
        $this->format = $format;
    }

    public static function createInstance($format)
    {
        return new DateFormat($format);
    }

    public function __invoke(FieldInterface $field)
    {
        $field->assignHelperCallback(
            'TableCell.Content',
            function (TableCell $helper, array $rowData, $rowIndex, $columnIndex) use ($field) {
                return $this->renderContent($field, $rowData, [$helper->getView(), 'escapeHtml']);
            }
        );

        $field->assignHelperCallback(
            'CsvCell.Content',
            function (CsvCell $helper, array $rowData, $rowIndex, $columnIndex) use ($field) {
                return $this->renderContent($field, $rowData);
            }
        );
    }

    private function renderContent(FieldInterface $field, array $rowData, callable $outputFilter = null)
    {
        if (!isset($rowData[$field->getName()]) || !$rowData[$field->getName()]) {
            return null;
        }

        $date   = new DateTimeImmutable($rowData[$field->getName()]);
        $output = $date->format($this->format);

        if ($outputFilter) {
            $output = $outputFilter($output);
        }

        return $output;
    }
}
