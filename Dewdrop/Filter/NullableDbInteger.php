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
 * This filter is intended to assist with DB integer fields that are allowed to
 * be nullable.
 */
class NullableDbInteger extends AbstractFilter
{
    /**
     * For empty strings (think HTTP requests, where everything is a string), or
     * actual nulls, we return null.  If not, we return the value cast to an int.
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
