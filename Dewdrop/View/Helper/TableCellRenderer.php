<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\TableCell;

/**
 * A conventient way to get a new \Dewdrop\Fields\Helper\TableCell
 * field helper while you're in a view script.
 */
class TableCellRenderer extends AbstractHelper
{
    /**
     * Return a new TableCell instance.
     *
     * @return TableCell
     */
    public function direct()
    {
        return new TableCell($this->view);
    }
}
