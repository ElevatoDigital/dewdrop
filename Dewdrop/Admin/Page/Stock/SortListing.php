<?php

namespace Dewdrop\Admin\Stock;

use Dewdrop\Admin\Page\PageAbstract;

class SortListing extends PageAbstract
{
    public function init()
    {
        if (!$this->component instanceof SortableListingInterface) {
            throw new Exception('Component must implement SortableListingInterface');
        }
    }
}
