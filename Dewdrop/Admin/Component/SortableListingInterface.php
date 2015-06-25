<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Component;

use Dewdrop\Db\Field as DbField;

/**
 * Implementing this interface on a CrudInterface component will make your
 * index/listing page user-sortable via drag and drop.
 */
interface SortableListingInterface
{
    /**
     * Get the field that stores your custom sort order values (e.g. sort_index).
     *
     * @return DbField
     */
    public function getSortField();
}
