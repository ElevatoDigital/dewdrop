<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\TableCell;
use Dewdrop\Fields\FieldInterface;

/**
 * This view helper assists with rendering handles that can be used to drag
 * records into a custom sorting order.
 */
class TableSortHandle extends AbstractHelper
{
    /**
     * Adjust the behavior of the supplied field in the context of the supplied
     * table renderer so that it draws a "handle" that the user can grab to
     * drag the row around.  Adds some custom JS/CSS to pull that off.
     *
     * @param FieldInterface $field
     * @param TableCell $cellRenderer
     * @param string $primaryKeyName
     * @return void
     */
    public function assignToField(FieldInterface $field, TableCell $cellRenderer, $primaryKeyName)
    {
        $this->view->headScript()
            ->appendFile($this->view->bowerUrl('/dewdrop/www/js/listing-sortable.js'));

        $this->view->headLink()
            ->appendStylesheet($this->view->bowerUrl('/dewdrop/www/css/listing-sortable.css'));

        $cellRenderer->getContentRenderer()->assign(
            $field->getId(),
            function ($helper, array $rowData) use ($primaryKeyName) {
                /* @var $helper TableCell\Content */
                return sprintf(
                    '<span data-id="%d" class="handle glyphicon glyphicon-align-justify"></span>',
                    $helper->getView()->escapeHtmlAttr($rowData[$primaryKeyName])
                );
            }
        );
    }

    /**
     * The open method should be called prior to rendering individual records
     * with the field you setup in assignToField().  This communicates to the JS
     * where the new sort order should be submitted.
     *
     * @param $ajaxUrl
     * @return string
     */
    public function open($ajaxUrl)
    {
        return sprintf(
            '<div data-dewdrop="listing-sortable" data-sort-url="%s">',
            $this->view->escapeHtmlAttr($ajaxUrl)
        );
    }

    /**
     * Close the wrapping div that was opened with open().
     *
     * @return string
     */
    public function close()
    {
        return '</div>';
    }
}
