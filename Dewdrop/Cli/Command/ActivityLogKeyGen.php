<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Exception;
use Dewdrop\Paths;
use Dewdrop\Pimple;

class ActivityLogKeyGen extends CommandAbstract
{
    public function init()
    {
        $this
            ->setCommand('activity-log-key-gen')
            ->addAlias('activity-log-keygen')
            ->setDescription('Generate secret key used for activity log cookie authentication.');
    }

    public function execute()
    {
        /* @var $paths Paths */
        $paths = Pimple::getResource('paths');
        $path  = $paths->getData() . '/activity-log';

        if (!file_exists($path) || !is_dir($path)) {
            mkdir($path, 0777);
        }

        $fullPath = $path . '/secret-session-cookie-key.php';

        if (file_exists($fullPath)) {
            throw new Exception('Key already present.  Delete the current key file if you want to regenerate.');
        } else {
            $key = bin2hex(random_bytes(64));
            file_put_contents($fullPath, $key, LOCK_EX);
        }
    }
}
