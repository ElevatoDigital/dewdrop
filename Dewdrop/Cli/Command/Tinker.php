<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Psy\Shell;
use Psy\Configuration;

/**
 * Provides a basic REPL you can use to experiment with Dewdrop.
 */
class Tinker extends CommandAbstract
{
    public function init()
    {
        $this
            ->setCommand('tinker')
            ->addAlias('repl')
            ->setDescription('Start a REPL where you can experiment with Dewdrop.');
    }

    public function execute()
    {
        $config = new Configuration();

        $shell = new Shell($config);
        $shell->run();
    }
}
