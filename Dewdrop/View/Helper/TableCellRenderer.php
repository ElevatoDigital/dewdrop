<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\TableCell;

class TableCellRenderer extends AbstractHelper
{
    public function direct()
    {
        return new TableCell($this->view->getEscaper());
    }
}
