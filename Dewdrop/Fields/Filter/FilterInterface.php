<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Filter;

use Dewdrop\Fields;

/**
 * Field filters should implement this interface to narrow down a set of fields
 * according to the criteria in the apply() method.
 */
interface FilterInterface
{
    /**
     * Filter the supplied Fields object according to the logic implemented by this
     * filter.
     *
     * @param Fields $fields
     * @return Fields
     */
    public function apply(Fields $fields);
}
