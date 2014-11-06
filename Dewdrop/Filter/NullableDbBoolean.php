<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Filter;

use Zend\Filter\AbstractFilter;

/**
 * This filter is intended to assist with DB boolean fields that are allowed to
 * be nullable, essentially making them a tri-state field: null, false, or true.
 * In the case of a checkbox input, obviously, you cannot really differentiate
 * between null and false, but you can when using something like a yes/no
 * select/dropdown.
 */
class NullableDbBoolean extends AbstractFilter
{
    /**
     * For empty strings (think HTTP requests, where everything is a string), or
     * actual nulls, we return null.  If not, we return the value cast to an int,
     * which is friendlier than dragging around the actual boolean because those
     * don't play nice with the DBs.
     *
     * @param mixed $value
     * @return int|null
     */
    public function filter($value)
    {
        if (null === $value || '' === $value) {
            $out = null;
        } else {
            $out = (int) $value;
        }

        return $out;
    }
}
