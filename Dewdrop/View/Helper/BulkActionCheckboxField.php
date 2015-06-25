<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Field;
use Dewdrop\Fields\Listing\BulkActions;
use Dewdrop\Fields\Listing\BulkActions\Exception;
use Dewdrop\Fields\Helper\TableCell as TableCellHelper;

/**
 * This helper will create a new custom field for you that is configured to be
 * rendered as a checkbox in the supplied table cell helper.  The checkbox will
 * use the ID from the BulkActions object so that the user can select items.
 * This helper will return the field object, allowing you to prepend/append it
 * to your overall Fields object before rendering.
 */
class BulkActionCheckboxField extends AbstractHelper
{
    /**
     * Create a field object, configured to render a checkbox for use in selecting
     * items for the supplied BulkActions object.
     *
     * @param BulkActions $bulkActions
     * @param TableCellHelper $renderer
     * @return Field
     * @throws Exception
     */
    public function direct(BulkActions $bulkActions, TableCellHelper $renderer)
    {
        $field = new Field();
        $key   = $bulkActions->getPrimaryKey()->getName();

        $field
            ->setId($bulkActions->getId())
            ->setVisible(true);

        $renderer->getHeaderRenderer()->assign(
            $bulkActions->getId(),
            function () {
                return '';
            }
        );

        $renderer->getContentRenderer()->assign(
            $bulkActions->getId(),
            function ($helper, array $rowData) use ($bulkActions, $key) {
                /* @var $helper \Dewdrop\Fields\Helper\TableCell\Content */

                if (!isset($rowData[$key])) {
                    throw new Exception("{$key} not available in row data for bulk action checkbox render.");
                }

                $value = $rowData[$key];

                return sprintf(
                    '<input class="bulk-checkbox" type="checkbox" name="%s[]" value="%s" %s />',
                    $helper->getView()->escapeHtmlAttr($bulkActions->getId()),
                    $helper->getView()->escapeHtmlAttr($value),
                    (in_array($value, $bulkActions->getSelected()) ? 'checked="checked"' : '')
                );
            }
        );

        return $field;
    }
}
