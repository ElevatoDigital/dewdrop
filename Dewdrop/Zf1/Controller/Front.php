<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1\Controller;

use Zend_Controller_Front;

class Front extends Zend_Controller_Front
{
    public static function hasInstance()
    {
        return null !== self::$_instance;
    }
}
