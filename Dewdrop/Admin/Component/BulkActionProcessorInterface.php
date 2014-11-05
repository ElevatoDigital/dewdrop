<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Fields\Listing\BulkActions;

interface BulkActionProcessorInterface
{
    /**
     * @return BulkActions
     */
    public function getBulkActions();
}
