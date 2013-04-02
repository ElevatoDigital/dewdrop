<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Filter;

use Zend\Filter\FilterInterface;

/**
 * Filter to run stripslashes() on supplied value.  Used primarily when working
 * with wp_editor() which bizarrely insists upon adding slashes to your input
 * as if magic quotes were still legit.
 */
class Stripslashes implements FilterInterface
{
    /**
     * Strip slashes from the supplied input value.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        return stripslashes($value);
    }
}
