<?php

namespace Dewdrop\Cli;

class Run
{
    protected $validCommands = array(
        array(
            'description' => 'Display the list of available commands',
            'callback'    => 'runHelp',
            'command'     => 'help'
        ),
        array(
            'description' => 'Run PHP_CodeSniffer on your plugin to ensure follows PSR-2',
            'callback'    => 'runSniff',
            'command'     => 'sniff',
            'aliases'     => array(
                'code-sniff',
                'cs'
            )
        ),
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

    public function run()
    {
        $subcommand = (isset($_SERVER['argv'][1]) ? strtolower($_SERVER['argv'][1]) : null);

        foreach ($this->validCommands as $validCommand) {
            if ($validCommand['command'] === $subcommand
                || (isset($validCommand['aliases']) && in_array($subcommand, $validCommand['aliases']))
            ) {
                $method = $validCommand['callback'];
                $this->$method();
                exit;
            }
        }

        echo PHP_EOL;
        echo 'ERROR: Please specify a valid command as the first argument.' . PHP_EOL;
        echo PHP_EOL;

        $this->runHelp();
        exit;
    }

    public function runHelp()
    {
        foreach ($this->validCommands as $validCommand) {
            printf(
                "%15s: %s" . PHP_EOL,
                $validCommand['command'],
                $validCommand['description']
            );
        }
    }

    public function runSniff()
    {
        $cmd = sprintf(
            'phpcs --standard=PSR2 --ignore=*/Zend/* --ignore=*/tests/* %s',
            escapeshellarg(
                dirname(dirname(dirname(__DIR__)))
            )
        );

        passthru($cmd);
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
