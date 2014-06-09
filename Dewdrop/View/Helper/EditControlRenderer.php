<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Fields\Helper\EditControl;

class EditControlRenderer extends AbstractHelper
{
    public function direct()
    {
        return new EditControl($this->view);
    }
}
