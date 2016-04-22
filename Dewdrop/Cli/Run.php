<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli;

use Dewdrop\Cli\Command\CommandAbstract;
use Dewdrop\Cli\Renderer\RendererInterface;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Pimple as DewdropPimple;
use Pimple;

/**
 * This class is responsible for handling execution of CLI commands.
 *
 * If you need to add CLI commands that are not provided by Dewdrop out of
 * the box, provide an array of command classnames in a Pimple resource
 * called "cli-commands".  We use this special Pimple resource for custom
 * command injection because it's not possible to define a custom Run
 * instance in Pimple altogether, like we do for custom view helpers for
 * example.  Run is a "root" class in the sense that it is responsible
 * for finding and kicking off the bootstrap directly.
 */
class Run
{
    /**
     * The class names of the commands that can be handled.
     *
     * @var array
     */
    private $commandClasses = array(
        'Help',
        'WpInit',
        'BuildMetadata',
        'Dbdeploy',
        'DbMetadata',
        'AuthHashPassword',
        'Lint',
        'GenAdminComponent',
        'GenDbTable',
        'GenEav',
        'Sniff',
        'Tinker',
        'ActivityLogKeyGen',
        'ActivityLogGeoIpDownload',
        'DewdropDev',
        'DewdropDoc',
        'DewdropSniff',
        'DewdropTest',
    );

    /**
     * The instantiated command objects available for execution.
     *
     * @var array()
     */
    private $commands = array();

    /**
     * The arguments that should be supplied to the executed command.  By
     * default these will be taken from the command line directly, using
     * every element in the $_SERVER['argv'] array starting at index 2.
     *
     * @var array
     */
    private $args = array();

    /**
     * The name of the command that should be run.  If not specified, the
     * command specified in $_SERVER['argv'][1] will be used.
     *
     * @var string
     */
    private $command;

    /**
     * The renderer that will be used by this class and any executed
     * commands in order to send output to the terminal.
     *
     * @var \Dewdrop\Cli\Renderer\RendererInterface
     */
    private $renderer;

    /**
     * The DB adapter.  You can retrieve this by calling connectDb().
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $dbAdapter;

    /**
     * A \Dewdrop\Paths instance to help in navigating the filesystem.
     *
     * @var \Dewdrop\Paths
     */
    private $paths;

    /**
     * The Pimple DI container associated with this application.  Found
     * via the application's bootstrap class.
     *
     * @var Pimple
     */
    private $pimple;

    /**
     * Create the CLI runner, giving users the ability to inject non-default
     * args, command name, and renderer (primarily for testing purposes).
     *
     * @param array $args
     * @param string $command
     * @param RendererInterface $renderer
     */
    public function __construct(array $args = null, $command = null, $renderer = null)
    {
        $this->pimple   = DewdropPimple::getInstance();
        $this->args     = ($args ?: array_slice($_SERVER['argv'], 2));
        $this->renderer = ($renderer ?: new Renderer\Markdown());
        $this->paths    = (isset($this->pimple['paths']) ? $this->pimple['paths'] : new Paths());

        if ($command) {
            $this->command = $command;
        } elseif (isset($_SERVER['argv'][1])) {
            $this->command = $_SERVER['argv'][1];
        }
    }

    /**
     * Grab the Pimple DI container associated with this application.
     *
     * @return Pimple
     */
    public function getPimple()
    {
        return $this->pimple;
    }

    /**
     * Override the args applied to this runner
     *
     * @param array $args
     * @return \Dewdrop\Cli\Run
     */
    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Find the selected command, if any, and execute it.  If no command is
     * selected, display the default help content instead.  Either way,
     * execution is halted after this method is done.
     *
     * @return void
     */
    public function run()
    {
        $this->instantiateCommands();

        /* @var $command CommandAbstract */
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

    /**
     * Stop execution.  In a separate method to make it easy to mock during
     * testing.
     *
     * @param integer $exitStatus
     * @return void
     */
    public function halt($exitStatus = 0)
    {
        exit($exitStatus);
    }

    /**
     * Run the named command, if its arguments can be successfully parsed.
     *
     * @param string $name
     * @return \Dewdrop\Cli\Run
     */
    public function executeCommand($name)
    {
        /* @var $command CommandAbstract */
        $command       = $this->commands[$name];
        $shouldExecute = $command->parseArgs($this->args);

        if ($shouldExecute) {
            $command->execute();
        }

        return $this;
    }

    /**
     * Get the array of instantiated commands.  Will be an empty array until
     * run() is called.  This is used by the Help command to display a list
     * of available commands.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Pull in the wp-config.php file to allow us to connect to the database
     * on the CLI.
     *
     * @return \Dewdrop\Db\Adapter
     */
    public function connectDb()
    {
        if (!$this->dbAdapter) {
            $this->dbAdapter = $this->pimple['db'];
        }

        return $this->dbAdapter;
    }

    /**
     * Instantiate all command objects.  We need them all instantiated so that
     * we can see if any has been selected for execution (i.e. the command
     * property of this object matches the command name or one of its aliases).
     *
     * @return void
     */
    protected function instantiateCommands()
    {
        foreach ($this->commandClasses as $commandClass) {
            $fullClassName = '\Dewdrop\Cli\Command\\' . $commandClass;

            $this->commands[$commandClass] = new $fullClassName($this, $this->renderer);
        }

        // Add any commands found in the project's commands folder
        $commandPath = $this->paths->getCommands();

        if (is_dir($commandPath)) {
            $commands = glob($commandPath . '/*.php');

            foreach ($commands as $command) {
                $className = '\\Command\\' . basename($command, '.php');

                $this->commands[$className] = new $className($this, $this->renderer);
            }
        }

        // Add any custom commands defined in Pimple's "cli-commands" resource
        if (isset($this->pimple['cli-commands'])) {
            foreach ($this->pimple['cli-commands'] as $className) {
                $this->commands[$className] = new $className($this, $this->renderer);
            }
        }
    }
}
