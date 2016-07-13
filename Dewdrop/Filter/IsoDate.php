<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Filter;

use DateTimeZone;
use Zend\Filter\AbstractFilter;

/**
 * A filter to taking anything that can be parsed by GNU Date (via strtotime())
 * and converting it to an ISO-formatted date that can be used for date values
 * in a DB, etc.
 */
class IsoDate extends AbstractFilter
{
    /**
     * Filter the supplied input string to an ISO date.
     *
     * @param string $value
     * @return null|string
     */
    public function filter($value)
    {
        if (null === $value || '' === $value) {
            $out = null;
        } else {
            $gmtOffset = 0;

            // Reverse WordPress GMT offset when filtering date input
            if (function_exists('get_option')) {
                $timezoneString = get_option('timezone_string');
                $isoValue       = date('Y-m-d', strtotime($value));

                if ($timezoneString) {
                    $gmtOffset = timezone_offset_get(new DateTimeZone($timezoneString), date_create($isoValue));
                }
            }

            $out = date('Y-m-d', strtotime($value) + ($gmtOffset * -1));
        }

        return $out;
    }
}
