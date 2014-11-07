<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

use Dewdrop\Fields\Listing\BulkActions;

/**
 * Conforming to this interface informs component pages that you can provide
 * a BulkActions object to process actions on multiple records of your listing
 * at once.
 */
interface BulkActionProcessorInterface
{
    /**
     * Provide a BulkActions object for processing multiple Listing records.
     *
     * @return BulkActions
     */
    public function getBulkActions();
}
