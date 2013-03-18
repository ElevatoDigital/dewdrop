<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 *
 * @category   Dewdrop
 * @package    Cli
 */

namespace Dewdrop\Cli;

use Dewdrop\Paths;
use Dewdrop\Db\Adapter;

/**
 * This class is responsible for handling execution of CLI commands.
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
        'Update',
        'Sniff',
        'Dbdeploy',
        'DbMetadata',
        'Lint',
        'GenAdminComponent',
        'GenDbTable',
        'DewdropTest',
        'DewdropDoc'
    );

    /**
     * The instantiated command objects available for execution.
     *
     * @var array()
     */
    private $commands = array();

    /**
     * The autoloader instance used for class loading.
     *
     * @var \Dewdrop\Autoloader
     */
    private $autoloader;

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
     * Create the CLI runner, giving users the ability to inject non-default
     * args, command name, and renderer (primarily for testing purposes).
     *
     * @param array $args
     * @param string $command
     * @param RendererInterface $renderer
     */
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
     * @return void
     */
    public function halt()
    {
        exit;
    }

    /**
     * Run the named command, if its arguments can be successfully parsed.
     *
     * @param string $name
     * @return \Dewdrop\Cli\Run
     */
    public function executeCommand($name)
    {
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
            $paths = new Paths();

            require_once $paths->getWpRoot() . '/wp-config.php';
            require_once $paths->getWpRoot() . '/wp-includes/wp-db.php';

            global $wpdb;

            $this->dbAdapter = new Adapter($wpdb);
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
            require_once __DIR__ . '/Command/' . $commandClass . '.php';
            $fullClassName = '\Dewdrop\Cli\Command\\' . $commandClass;

            $this->commands[$commandClass] = new $fullClassName($this, $this->renderer);
        }
    }
}
