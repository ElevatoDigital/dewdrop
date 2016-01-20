<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\Helper\TableCell\Content;
use Dewdrop\Fields\Helper\TableCell\Header;
use Dewdrop\Fields\Helper\TableCell\TdClassNames;
use Dewdrop\View\View;

/**
 * The TableCell helper actually composes 3 simpler helpers that are useful when
 * customizing the rendering an HTML table:
 *
 * 1. TableCell.Content: This helper will allow you to customize the content of
 *    your table's &lt;td&gt; tags.
 *
 * 2. TableCell.Header: This helper allows you to customize the content of your
 *    table's &lt;th&gt; tags, if you want to display anything other than just
 *    the field's labels.
 *
 * 3. TableCell.TdClassNameRenderer: This helper allows you to add one or more
 *    classes to your table's &lt;td&gt; tags.
 *
 * Customizing the rendering of your cell's content in a view script:
 *
 * <pre>
 * $renderer = $this->tableCellRenderer();
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
 *             'TableCell:Header',
 *             function ($helper, $field) {
 *                 return 'Return a custom string rather than using the field label';
 *             }
 *         );
 *     }
 * );
 * </pre>
 */
class TableCell implements CellRendererInterface
{
    /**
     * The helper used to render &lt;td&gt; content.
     *
     * @var \Dewdrop\Fields\Helper\TableCell\Content
     */
    private $contentRenderer;

    /**
     * The helper used to render &lt;th&gt; content.
     *
     * @var \Dewdrop\Fields\Helper\TableCell\Header
     */
    private $headerRenderer;

    /**
     * The helper used to build a CSS class string for a &lt;td&gt;.
     *
     * @var TdClassNames
     */
    private $tdClassNamesRenderer;

    /**
     * A callback used to get an array of CSS class name for a table row.
     *
     * @var callable
     */
    private $rowClassCallback;

    /**
     * Provide a \Dewdrop\View\View hat can be used by callbacks to escape
     * their output to prevent XSS attacks or call helpers to render their
     * output.
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $view = ($view ?: new View());

        $this->contentRenderer      = new Content($view);
        $this->headerRenderer       = new Header($view);
        $this->tdClassNamesRenderer = new TdClassNames($view);
    }

    /**
     * Get the content renderer so you can assign custom callbacks or render your
     * table.
     *
     * @return \Dewdrop\Fields\Helper\TableCell\Content
     */
    public function getContentRenderer()
    {
        return $this->contentRenderer;
    }

    /**
     * Get the header renderer so you can assign custom callbacks or render your
     * table headers.
     *
     * @return \Dewdrop\Fields\Helper\TableCell\Header
     */
    public function getHeaderRenderer()
    {
        return $this->headerRenderer;
    }

    /**
     * Get the TD class names renderer so you can assign custom callbacks and
     * generate class strings.
     *
     * @return TdClassNames
     */
    public function getTdClassNamesRenderer()
    {
        return $this->tdClassNamesRenderer;
    }

    /**
     * Provide a callback that can be used to generate an array of CSS class
     * names that should be applied to a table row.
     *
     * @param callable $callback
     * @return $this
     */
    public function setRowClassCallback(callable $callback)
    {
        $this->rowClassCallback = $callback;

        return $this;
    }

    /**
     * Get an array of CSS class names that should be applied to the given row.
     *
     * @param array $rowData
     * @return array
     */
    public function getRowClasses(array $rowData)
    {
        if (!$this->rowClassCallback) {
            return [];
        } else {
            return call_user_func($this->rowClassCallback, $rowData);
        }
    }
}
