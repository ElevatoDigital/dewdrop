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

    private $view;

    private $detector;

    public function __construct(View $view, EditHelperDetector $detector = null)
    {
        $this->view     = $view;
        $this->detector = ($detector ?: new EditHelperDetector());
    }

    public function getView()
    {
        return $this->view;
    }

    public function render(FieldInterface $field, $fieldPosition)
    {
        $callable = $this->getFieldAssignment($field);

        return call_user_func($callable, $this->view, $fieldPosition);
    }

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
