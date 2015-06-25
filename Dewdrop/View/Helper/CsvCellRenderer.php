<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\CsvCell;

/**
 * A conventient way to get a new \Dewdrop\Fields\Helper\CsvCell
 * field helper while you're in a view script.
 */
class CsvCellRenderer extends AbstractHelper
{
    /**
     * Return a new CsvCell helper instance.
     *
     * @return CsvCell
     */
    public function direct()
    {
        return new CsvCell();
    }
}
