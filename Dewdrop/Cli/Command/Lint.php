<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

/**
 * Check all PHP files in your plugin to ensure that have no syntax errors
 * using the built-in PHP linter.
 *
 * @todo Just a stub.  Not yet implemented.
 */
class Lint extends CommandAbstract
{
    /**
     * The path to the php binary
     *
     * @var string
     */
    private $php;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Check the syntax of all PHP files')
            ->setCommand('lint')
            ->addAlias('check-php');

        $this->addArg(
            'php',
            'The path to the php binary',
            self::ARG_OPTIONAL
        );
    }

    /**
     * Use "php -l" to check syntax of all ".php" files in the plugin.
     *
     * @return void
     */
    public function execute()
    {

    }
}
