<?php

namespace Dewdrop\Cli\Command;

class Help extends CommandAbstract
{
    private $subcommand;

    public function init()
    {
        $this
            ->setDescription('Display the list of available commands')
            ->setCommand('help');

        $this->addPrimaryArg(
            'subcommand',
            'The command you would like additional help with',
            self::ARG_OPTIONAL
        );

        $this->addExample(
            'Get additional information about the "lint" command',
            './dewdrop help lint'
        );
    }

    public function setSubcommand($subcommand)
    {
        $this->subcommand = $subcommand;

        return $this;
    }

    public function execute()
    {
        if (!$this->subcommand) {
            $this->displayGlobalHelp();
        } else {
            $this->displayCommandHelp();
        }
    }

    public function displayGlobalHelp()
    {
        $longestCommand = 0;

        foreach ($this->runner->getCommands() as $command) {
            $commandName = $command->getCommand();

            if (strlen($commandName) > $longestCommand) {
                $longestCommand = strlen($commandName);
            }
        }

        $this->renderer->title('Available Commands');

        $rows = array();

        foreach ($this->runner->getCommands() as $command) {
            $rows[$command->getCommand()] = $command->getDescription();
        }

        $this->renderer->table($rows);
    }

    public function displayCommandHelp()
    {
        foreach ($this->runner->getCommands() as $command) {
            if ($command->isSelected($this->subcommand)) {
                $command->help();
                return;
            }
        }

        $this->renderer
            ->error('Could not find command "' . $this->subcommand . '" for help.')
            ->newline();

        $this->displayGlobalHelp();
    }
}
