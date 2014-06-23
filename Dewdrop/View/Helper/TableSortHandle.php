<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\TableCell;
use Dewdrop\Fields\FieldInterface;

class TableSortHandle extends AbstractHelper
{
    public function assignToField(FieldInterface $field, TableCell $cellRenderer, $primaryKeyName)
    {
        $this->view->headScript()
            ->appendFile($this->view->bowerUrl('/jquery-ui/ui/minified/jquery-ui.min.js'))
            ->appendFile($this->view->bowerUrl('/dewdrop/www/js/listing-sortable.js'));

        $this->view->headLink()
            ->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/listing-sortable.css'));

        $cellRenderer->getContentRenderer()->assign(
            $field->getId(),
            function ($helper, array $rowData) use ($primaryKeyName) {
                return sprintf(
                    '<span data-id="%d" class="handle glyphicon glyphicon-align-justify"></span>',
                    $helper->getEscaper()->escapeHtmlAttr($rowData[$primaryKeyName])
                );
            }
        );
    }

    public function open($ajaxUrl)
    {
        return sprintf(
            '<div data-dewdrop="listing-sortable" data-sort-url="%s">',
            $this->view->escapeHtmlAttr($ajaxUrl)
        );
    }

    public function close()
    {
        return '</div>';
    }
}
