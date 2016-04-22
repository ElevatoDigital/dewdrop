<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\ActivityLog;

use Dewdrop\Paths;
use Dewdrop\Pimple;

class SecretCookieSigningKey
{
    private static $key = false;

    public static function get()
    {
        if (!self::$key) {
            $paths   = (Pimple::hasResource('paths') ? Pimple::getResource('paths') : new Paths());
            $keyFile = $paths->getData() . '/activity-log/secret-session-cookie-key.php';

            if (file_exists($keyFile) && is_readable($keyFile)) {
                self::$key = trim(file_get_contents($keyFile));
            }
        }

        return self::$key;
    }
}
