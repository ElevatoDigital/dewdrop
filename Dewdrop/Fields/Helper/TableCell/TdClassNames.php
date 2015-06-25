<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\TableCell;

use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;
use Dewdrop\View\View;

/**
 * The TdClassNames helper allows you to render a CSS class string that will
 * be added to the &lt;td&gt; of your table cell.  When defining a custom
 * callback for this helper, using the following callback parameters:
 *
 * <pre>
 * $tableCell->getContentRenderer()->assign(
 *     'my_model:my_field',
 *     function ($helper, array $rowData, $rowIndex, $columnIndex) {
 *         // Param $helper is a reference to this instance of the Header helper
 *         // Param $rowData if an associative array representing all the data available to render this table row.
 *         // Param $rowIndex is a zero-based index of the current row being rendered
 *         // Param $columnIndex is a zero-based index of the current column being rendered
 *
 *         return '<strong>' . $helper->getView()->escapeHtml($row['my_field']) . '</strong>;
 *     }
 * );
 * </pre>
 */
class TdClassNames extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'tablecell.tdclassnames';

    /**
     * A view object used for rendering and escaping.
     *
     * @var View
     */
    private $view;

    /**
     * Provide a Dewdrop view for rendering.
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Get the Dewdrop view object that can be used to render the cell's content
     * and escape it to prevent XSS.
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * The TableCell helpers originally used an Escaper rather than a Dewdrop
     * view object.  This was limiting and also error-prone because Escaper
     * fails to handle nulls well.  The view API has all of Escaper's public
     * methods, though, so returning it here, should not break any code.
     *
     * @return \Dewdrop\View\View
     * @deprecated
     */
    public function getEscaper()
    {
        return $this->view;
    }

    /**
     * Render a CSS class string for the field's table cell.  Your callback
     * can return either a string or an array of classes.
     *
     * @param FieldInterface $field
     * @param array $rowData
     * @param int $rowIndex
     * @param int $columnIndex
     * @return string
     */
    public function render(FieldInterface $field, array $rowData, $rowIndex, $columnIndex)
    {
        $callable = $this->getFieldAssignment($field);
        $output   = call_user_func($callable, $rowData, $rowIndex, $columnIndex);

        if (!is_array($output)) {
            return '';
        } else {
            return implode(
                ' ',
                array_map(
                    array($this->view, 'escapeHtmlAttr'),
                    $output
                )
            );
        }
    }

    /**
     * By default, we just return an empty string if there was not custom
     * callback found for a field.
     *
     * @param FieldInterface $field
     * @return callable
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return function () {
            return '';
        };
    }
}
