<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\CsvCell;

/**
 */
class CsvCellRenderer extends AbstractHelper
{
    /**
     * @return CsvCell
     */
    public function direct()
    {
        return new CsvCell();
    }
}
