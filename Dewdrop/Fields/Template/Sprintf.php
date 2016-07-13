<?php

namespace Dewdrop\Fields\Template;

use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\CsvCell\Content as CsvCell;
use Dewdrop\Fields\Helper\TableCell\Content as TableCell;

class Sprintf
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public static function createInstance($template)
    {
        return new Sprintf($template);
    }

    public function __invoke(FieldInterface $field)
    {
        $field->addHelperFilter(
            'TableCell.Content',
            function ($content, TableCell $helper, array $rowData, $rowIndex, $columnIndex) {
                if (!$content) {
                    return $content;
                }
                
                return $helper->getView()->escapeHtml(sprintf($this->template, $content));
            }
        );

        $field->addHelperFilter(
            'CsvCell.Content',
            function ($content, CsvCell $helper, array $rowData, $rowIndex, $columnIndex) {
                if (!$content) {
                    return $content;
                }

                return sprintf($this->template, $content);
            }
        );
    }
}
