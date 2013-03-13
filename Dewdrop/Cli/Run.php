<?php

namespace Dewdrop\Cli;

class Run
{
    protected $validCommands = array(
        array(
            'description' => 'Run PHPUnit on the Dewdrop unit tests',
            'callback'    => 'runDewdropTests',
            'command'     => 'test-dewdrop',
            'aliases'     => array(
                'dewdrop-test',
                'dewdrop-tests'
            )
        ),
        array(
            'description' => "Run PHPUnit on your plugin's unit tests",
            'command'     => 'test',
            'callback'    => 'runTests',
            'aliases'     => array(
                'tests',
                'phpunit'
            )
        )
    );

    private $commandClasses = array(
        'Help',
        'Update',
        'Sniff',
        'Dbdeploy',
        'Lint',
        'GenAdminComponent',
        'GenDbTable'
    );

    private $commands = array();

    private $autoloader;

    private $args = array();

    public function __construct(array $args = null)
    {
        $this->args = ($args ?: array_slice($_SERVER['argv'], 2));

        require_once dirname(__DIR__) . '/Autoloader.php';
        $this->autoloader = new \Dewdrop\Autoloader(dirname(dirname(__DIR__)));
    }

    public function run()
    {
        $subcommand   = (isset($_SERVER['argv'][1]) ? strtolower($_SERVER['argv'][1]) : null);
        $commandFound = false;

        $this->instantiateCommands();

        foreach ($this->commands as $command) {
            if ($command->isSelected($subcommand)) {
                $command
                    ->parseArgs($this->args)
                    ->execute();

                $commandFound = true;
            }
        }

        if (!$commandFound) {
            echo PHP_EOL;
            echo 'ERROR: Please specify a valid command as the first argument.' . PHP_EOL;
            echo PHP_EOL;

            $this->commands['Help']->execute();
            exit;
        }
    }

    public function getCommands()
    {
        return $this->commands;
    }

    protected function instantiateCommands()
    {
        foreach ($this->commandClasses as $commandClass) {
            require_once __DIR__ . '/Command/' . $commandClass . '.php';
            $fullClassName = '\Dewdrop\Cli\Command\\' . $commandClass;

            $this->commands[$commandClass] = new $fullClassName($this);
        }
    }

    public function runTests()
    {
        $cmd = sprintf(
            'phpunit %s',
            escapeshellarg(
                dirname(dirname(dirname(__DIR__))) . '/tests'
            )
        );

        passthru($cmd);
    }

    public function runDewdropTests()
    {
        $cmd = sprintf(
            'phpunit %s',
            escapeshellarg(
                dirname(dirname(__DIR__)) . '/tests'
            )
        );

        passthru($cmd);
    }
}
