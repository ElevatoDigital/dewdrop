<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Paths;

class Config
{
    private $data = array();

    public function __construct($file = null)
    {
        $paths = new Paths();

        if (!$paths->isWp()) {
            if (null === $file) {
                $file  = $paths->getPluginRoot() . '/dewdrop-config.php';
            }

            if (file_exists($file) || is_readable($file)) {
                $this->data = require $file;
            }
        } else {
            $this->data = array(
                'bootstrap' => '\Dewdrop\Bootstrap\Wp',
                'db' => array(
                    'username' => DB_USER,
                    'password' => DB_PASSWORD,
                    'host'     => DB_HOST,
                    'name'     => DB_NAME,
                    'type'     => 'mysql'
                )
            );
        }
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }
}
