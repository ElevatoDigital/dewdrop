<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;
use Dewdrop\Fields\EditHelperDetector;

/**
 * This helper wraps a \Dewdrop\Fields\EditHelperDetector to make it easy to
 * detect a suitable view helper for a \Dewdrop\Db\Field object at runtime in
 * your view script.  You can override the default helper for a field by calling
 * the customize method.
 */
class DetectEditHelper extends AbstractHelper
{
    /**
     * The \Dewdrop\Fields\EditHelperDetector that will be used to determine
     * which view helper would be suitable for a given field.
     *
     * @var \Dewdrop\Fields\EditHelperDetector
     */
    private $detector;

    /**
     * Override the default view helper for the specified field.  You can use
     * either a \Dewdrop\Db\Field object or a field control name for the $field
     * parameter.
     *
     * @param mixed $field
     * @param string $helperName
     * @return \Dewdrop\View\Helper\DetectEditHelper
     */
    public function customize($field, $helperName)
    {
        $this->getDetector()->customizeField($field, $helperName);

        return $this;
    }

    /**
     * Render the view helper for the supplied field.
     *
     * @param Field $field
     * @return string
     */
    public function render(Field $field)
    {
        $helperName = $this->getDetector()->detect($field);

        return $this->view->$helperName($field);
    }

    /**
     * Override the default EditHelperDetector object.  Primarily helpful
     * during testing.
     *
     * @param EditHelperDetector $editHelperDetector
     * @return \Dewdrop\View\Helper\DetectEditHelper
     */
    public function setDetector(EditHelperDetector $editHelperDetector)
    {
        $this->detector = $editHelperDetector;

        return $this;
    }

    /**
     * Get the \Dewdrop\Fields\EditHelperDetector object for use in this helper.
     * We lazy-load the detector whenever render() or customize() are first
     * called.
     *
     * @return \Dewdrop\Fields\EditHelperDetector
     */
    private function getDetector()
    {
        if (!$this->detector) {
            $this->detector = new EditHelperDetector();
        }

        return $this->detector;
    }
}
