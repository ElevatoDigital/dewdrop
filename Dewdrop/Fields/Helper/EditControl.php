<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\Helper\EditControl\Control;
use Dewdrop\Fields\Helper\EditControl\Label;
use Dewdrop\View\View;

/**
 * This helper manages the rendering of edit controls for fields.  It composes
 * a helper for the control itself and a helper for its label.  These helpers
 * have access to a View object, so they can use view helpers for their control
 * rendering.
 *
 * You can customize the rendering of a control for a field by writing a custom
 * callback like this:
 *
 * <pre>
 * $helper->getControlRenderer()->assign(
 *     $field,
 *     function ($helper, $view, $fieldPosition) use ($field) {
 *         return $view->inputText($field);
 *     }
 * );
 * </pre>
 */
class EditControl
{
    /**
     * The renderer that will be used to draw the control itself.
     *
     * @var Control
     */
    private $controlRenderer;

    /**
     * The renderer that will be used to draw the label itself.
     *
     * @var Label
     */
    private $labelRenderer;

    /**
     * The View into which the control will be rendered.
     *
     * @var \Dewdrop\View\View
     */
    private $view;

    /**
     * Provide the View into which the HTML will be rendered and create the
     * Control and Label renderers.
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;

        $this->controlRenderer = new Control($view);
        $this->labelRenderer   = new Label($view);
    }

    /**
     * Get the renderer used to draw the actual control.
     *
     * @return Control
     */
    public function getControlRenderer()
    {
        return $this->controlRenderer;
    }

    /**
     * Get the renderer used to draw the label.
     *
     * @return Label
     */
    public function getLabelRenderer()
    {
        return $this->labelRenderer;
    }
}
