<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Test;

use PHPUnit_Framework_TestCase;
use Zend\Dom\Query as DomQuery;

/**
 * A simple PHPUnit test case extension that provides some methods for checking
 * HTML output with CSS DOM queries.
 */
abstract class BaseTestCase extends PHPUnit_Framework_TestCase
{
    use DomAssertions;
}
