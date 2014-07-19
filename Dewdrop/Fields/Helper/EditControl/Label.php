<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\EditControl;

use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;
use Dewdrop\View\View;

/**
 * Render the label needed for a given field's edit control.  You don't need to worry
 * about rendering the actual &lt;label&gt; HTML tag, just the text that will serve
 * as its contents.
 */
class Label extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'editcontrol.label';

    /**
     * The View the label will be rendered into.
     *
     * @var View
     */
    private $view;

    /**
     * Provide the View this label will be rendered into.
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Get the View this label will be rendered into.
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Render the label for the supplied field.
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
     * method to find a suitable callback.  In the case of the Label helper,
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
