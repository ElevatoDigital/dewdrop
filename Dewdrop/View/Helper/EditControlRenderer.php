<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\EditControl;

/**
 * A conventient way to get a new \Dewdrop\Fields\Helper\EditControl
 * field helper while you're in a view script.
 */
class EditControlRenderer extends AbstractHelper
{
    /**
     * Return a new EditControl instance.
     *
     * @return EditControl
     */
    public function direct()
    {
        return new EditControl($this->view);
    }
}
