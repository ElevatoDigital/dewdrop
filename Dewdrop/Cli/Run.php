<?php

namespace Dewdrop\Cli;

class Run
{
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

    private $command;

    private $renderer;

    public function __construct(array $args = null, $command = null, $renderer = null)
    {
        require_once dirname(__DIR__) . '/Autoloader.php';
        $this->autoloader = new \Dewdrop\Autoloader(dirname(dirname(__DIR__)));

        $this->args     = ($args ?: array_slice($_SERVER['argv'], 2));
        $this->renderer = ($renderer ?: new Renderer\Markdown());

        if ($command) {
            $this->command = $command;
        } elseif (isset($_SERVER['argv'][1])) {
            $this->command = $_SERVER['argv'][1];
        }
    }

    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }

    public function run()
    {
        $this->instantiateCommands();

        foreach ($this->commands as $name => $command) {
            if ($command->isSelected($this->command)) {
                $this->executeCommand($name);
                $this->halt();
                return;
            }
        }

        $this->renderer->error('Please specify a valid command as the first argument.');
        $this->executeCommand('Help');
        $this->halt();
    }

    public function halt()
    {
        exit;
    }

    public function executeCommand($name)
    {
        $command       = $this->commands[$name];
        $shouldExecute = $command->parseArgs($this->args);

        if ($shouldExecute) {
            $command->execute();
        }

        return $this;
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

            $this->commands[$commandClass] = new $fullClassName($this, $this->renderer);
        }
    }
}
