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
 * A command to handle display of help information for other commands or
 * list available commands if none was selected by the command line
 * arguments.
 *
 * When the CLI runner can't find a valid command, this command is
 * executed to help the user fix their input.
 */
class Help extends CommandAbstract
{
    /**
     * The command help content should be displayed for.
     *
     * @var string
     */
    private $subcommand;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
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
            './vendor/bin/dewdrop help lint'
        );
    }

    /**
     * Set the sub-command (the command for which you want to display help
     * content)
     *
     * @param string $subcommand
     * @return \Dewdrop\Cli\Command\Help
     */
    public function setSubcommand($subcommand)
    {
        $this->subcommand = $subcommand;

        return $this;
    }

    /**
     * If a subcommand is specified, display help content for that specific
     * command.  Otherwise, display the global list of available commands.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->subcommand) {
            $this->displayGlobalHelp();
        } else {
            $this->displayCommandHelp();
        }
    }

    /**
     * Display the global list of available commands.
     *
     * @return void
     */
    public function displayGlobalHelp()
    {
        $this->renderer->title('Available Commands');

        $rows = array();

        foreach ($this->runner->getCommands() as $command) {
            $rows[$command->getCommand()] = $command->getDescription();
        }

        $this->renderer->table($rows);
    }

    /**
     * Display help content for a specific command, if a matching command
     * object is available in the runner.  Otherwise, display an error
     * message and the global help content instead.
     *
     * @return void
     */
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
