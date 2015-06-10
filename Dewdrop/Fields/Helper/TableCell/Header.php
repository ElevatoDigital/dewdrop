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
use Dewdrop\Fields\Helper\CellRenderer\HeaderHelperInterface;
use Dewdrop\View\View;

/**
 * The header helper allows you to render the content of the header for
 * a given field in a table (typically the &lt;th&gt; tag).  When defining
 * a custom callback for this helper, using the following callback
 * parameters:
 *
 * <pre>
 * $tableCell->getHeaderRenderer()->assign(
 *     'my_model:my_field',
 *     function ($helper, $field) {
 *         // Param $helper is a reference to this instance of the Header helper
 *         // Param $field is your Field object, so you can get the label, etc.
 *
 *         return $helper->getView()->escapeHtml($field->getLabel());
 *     }
 * );
 * </pre>
 *
 * NOTE: You do not have to supply a callback for this helper for any fields.
 * By default, this helper will just use the field's label for the header
 * content, so you only need to supply a custom callback if the field label
 * is not appropriate.
 */
class Header extends HelperAbstract implements HeaderHelperInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'tablecell.header';

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
     * Render the header content for the supplied field.
     *
     * @param FieldInterface $field
     * @return string
     */
    public function render(FieldInterface $field)
    {
        $callable = $this->getFieldAssignment($field);

        return call_user_func($callable);
    }

    /**
     * If no custom callback is defined for a field, it will fall back to this
     * method to find a suitable callback.  In the case of the Header helper,
     * we fall back to all field's just returning their label.
     *
     * @param FieldInterface $field
     * @return callable
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return function () use ($field) {
            return $this->view->escapeHtml($field->getLabel());
        };
    }
}
