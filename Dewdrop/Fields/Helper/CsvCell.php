<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\Helper\CsvCell\Content;
use Dewdrop\Fields\Helper\CsvCell\Header;

/**
 * The CsvCell helper actually composes 2 simpler helpers that are useful when
 * customizing the rendering of a CSV export:
 *
 * 1. CsvCell.Content: This helper will allow you to customize the content of
 *    your CSV export's data rows.
 *
 * 2. CsvCell.Header: This helper allows you to customize the content of your
 *    CSV export's header row, if you want to display anything other than just
 *    the field's labels.
 *
 * Customizing the rendering of your cell's content in a view script:
 *
 * <pre>
 * $renderer = $this->csvCellRenderer();
 *
 * $renderer->getContentRenderer()->assign(
 *     'my_table_name:my_field',
 *     function ($helper, array $rowData, $rowIndex, $columnIndex) {
 *         return $helper->getEscaper()->escapeHtml($rowData['my_field']);
 *     }
 * );
 * </pre>
 *
 * Customizing the rendering of a header in your model (typically in the init()
 * method of a \Dewdrop\Db\Table sub-class):
 *
 * <pre>
 * $this->customizeField(
 *     'my_field',
 *     function ($field) {
 *         $field->assignHelperCallback(
 *             'CsvCell:Header',
 *             function ($helper, $field) {
 *                 return 'Return a custom string rather than using the field label';
 *             }
 *         );
 *     }
 * );
 * </pre>
 */
class CsvCell implements CellRendererInterface
{
    /**
     * The helper used to render content.
     *
     * @var Content
     */
    protected $contentRenderer;

    /**
     * The helper used to render header.
     *
     * @var Header
     */
    protected $headerRenderer;

    /**
     * Creates header and content cell helpers
     *
     * @return void
     */
    public function __construct()
    {
        $this->contentRenderer = new Content();
        $this->headerRenderer  = new Header();
    }

    /**
     * Get the content renderer so you can assign custom callbacks or render your
     * CSV.
     *
     * @return Content
     */
    public function getContentRenderer()
    {
        return $this->contentRenderer;
    }

    /**
     * Get the header renderer so you can assign custom callbacks or render your
     * table headers.
     *
     * @return Header
     */
    public function getHeaderRenderer()
    {
        return $this->headerRenderer;
    }
}
