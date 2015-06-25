<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper\EditControl;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\EditHelperDetector;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;
use Dewdrop\View\View;

/**
 * Render the actual control needed to edit a specific field.  You don't have
 * to worry about rendering the label or error messages, just the actual control
 * (e.g. the text input).
 */
class Control extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'editcontrol.control';

    /**
     * The view in which this control will be rendered.  Can be used to access
     * view helpers, etc.
     *
     * @var \Dewdrop\View\View
     */
    private $view;

    /**
     * An EditHelperDetector object that can be used to provide reasonable
     * defaults for database fields.
     *
     * @var \Dewdrop\Fields\EditHelperDetector
     */
    private $detector;

    /**
     * Supply a View and EditorHelperDetector object for use by this helper.
     *
     * @param View $view
     * @param EditHelperDetector $detector
     */
    public function __construct(View $view, EditHelperDetector $detector = null)
    {
        $this->view     = $view;
        $this->detector = ($detector ?: new EditHelperDetector());
    }

    /**
     * Get the View this helper will render into.
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Render the control for the supplied field.  The field's position in the form
     * is provided (as a zero-indexed integer) so that you can alter the control's
     * HTML depending upon where it falls in the list of fields.  This can be useful,
     * for example, if you want to automatically focus the first field on a form.
     *
     * @param FieldInterface $field
     * @param $fieldPosition
     * @return mixed
     */
    public function render(FieldInterface $field, $fieldPosition)
    {
        $callable = $this->getFieldAssignment($field);

        return call_user_func($callable, $this->view, $fieldPosition);
    }

    /**
     * Detect a reasonable default behavior for DB fields using an
     * EditHelperDetector.
     *
     * @param FieldInterface $field
     * @return bool|callable|mixed
     */
    public function detectCallableForField(FieldInterface $field)
    {
        if (!$field instanceof DbField) {
            return false;
        }

        $autofocusHelpers = array('inputText', 'textarea');

        return function ($helper, $view, $fieldPosition) use ($field, $autofocusHelpers) {
            $viewHelper = $this->detector->detect($field);

            if (0 === $fieldPosition && in_array($viewHelper, $autofocusHelpers)) {
                return $this->view->$viewHelper($field, array('autofocus' => true));
            } else {
                return $this->view->$viewHelper($field);
            }
        };
    }
}
