<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\RendererInterface;
use Dewdrop\Exception;
use Dewdrop\Paths;

/**
 * The abstract class used by all CLI commands.
 *
 * This abstract class supplies CLI commands with argument parsing, help
 * content display, alias support, etc.
 */
abstract class CommandAbstract
{
    /**
     * Command argument is required.
     *
     * @const
     */
    const ARG_REQUIRED = true;

    /**
     * Command argument is optional.
     *
     * @const
     */
    const ARG_OPTIONAL = false;

    /**
     * The \Dewdrop\Cli\Run instance that is managing this and other CLI
     * commands.

     * @var \Dewdrop\Cli\Run
     */
    protected $runner;

    /**
     * The renderer that should be used for all command output.  No output
     * should be rendered directly (i.e. with echo, print, printf, etc.),
     * so that it is easier to capture and examine output during testing.
     *
     * @var \Dewdrop\Cli\Renderer\RendererInterface
     */
    protected $renderer;

    /**
     * Paths utility to assist CLI commands in getting around the WP
     * environment.
     *
     * @var \Dewdrop\Paths
     */
    protected $paths;

    /**
     * The command name that should be used on the CLI to select this
     * command class for execution.  For example, when running the
     * dewdrop CLI tool like this:
     *
     * ./vendor/bin/dewdrop command-name
     *
     * \Dewdrop\Cli\Run will select the command class that has a command
     * class that has a $command property value of "command-name"
     *
     * @var string
     */
    private $command;

    /**
     * A brief, 8-12 word description of this command's purpose.  This will
     * be displayed in the command's own help content and the global list
     * of available commands.
     *
     * @var string
     */
    private $description;

    /**
     * Any aliases that can be used to trigger this command in addition to
     * the primary command name.
     *
     * @var array
     */
    private $aliases = array();

    /**
     * The name of the command's primary argument.  The primary argument's
     * value can be specified without naming the argument explicitly on the
     * command line.  Using popular version control system Subversion as an
     * example, you can do a code checkout without specifying the name of the
     * path argument like this:
     *
     * svn checkout http://example.org/path
     *
     * In that case, the path argument is the primary argument of SVN's
     * checkout command.
     *
     * In Dewdrop, if your command had a primary argument of "name" and the
     * user supplied this input:
     *
     * ./vendor/bin/dewdrop my-command --folder=example "Example Name Value"
     *
     * The argument parser would set the name argument's value to "Example Name
     * Value" because that is the value expression not explicitly assigned to
     * another argument name.
     *
     * Users can still explicitly set the argument name for the primary argument,
     * too, if they prefer:
     *
     * ./vendor/bin/dewdrop --name="Example Name Value"
     *
     * @var string
     */
    private $primaryArg;

    /**
     * The arguments that are available for this command.
     *
     * @var array
     */
    private $args = array();

    /**
     * Whether this command supports passing along any unrecognized arguments to
     * another command.  For example, if your CLI command is a thin wrapper
     * around phpunit, you could pass all unrecognized arguments to phpunit so
     * that the Dewdrop user has the full capabilities of phpunit available to
     * them.
     *
     * @var boolean
     */
    private $supportFallbackArgs = false;

    /**
     * Any unrecognized arguments that should be passed along to the an
     * underlying/wrapped command.
     *
     * @var array
     */
    private $fallbackArgs = array();

    /**
     * Examples of valid usage for this command.
     *
     * @var array
     */
    private $examples = array();

    /**
     * Instantiate command with a runner and renderer.  After the command
     * sub-class runs its init() method, check to ensure the required basic
     * properties were set.
     *
     * @param Run $runner
     * @param RendererInterface $renderer
     */
    public function __construct(Run $runner, RendererInterface $renderer)
    {
        $this->runner   = $runner;
        $this->renderer = $renderer;
        $this->paths    = $runner->getPimple()['paths'];

        // All commands support the --help argument
        $this->addArg(
            'help',
            'Display the help message for this command',
            self::ARG_OPTIONAL
        );

        $this->init();

        if (!$this->command || !$this->description) {
            throw new Exception('You must set the name and description in your init() method.');
        }
    }

    /**
     * Implement the init() method in your command sub-class to set required
     * properties.  You'll likely call:
     *
     * - setCommand()
     * - setDescription()
     * - addAlias()
     * - addArg()
     * - addPrimaryArg()
     * - addExample()
     *
     * @return void
     */
    abstract public function init();

    /**
     * Run your command.  This will only be called if parseArgs() returns true,
     * indicating that the command line arguments could be successfully parsed
     * according the definitions you created in your init() method.
     */
    abstract public function execute();

    /**
     * Parse the arguments passed to this command.
     *
     * If the "--help" argument is present anywhere in the argument input, all
     * further parsing will be aborted and the command's help content will be
     * displayed.
     *
     * For all argument names and their aliases, there are multiple acceptable
     * formats of argument and value.  For example, all of these inputs are
     * equivalent:
     *
     * ./vendor/bin/dewdrop my-command --argument-name=value
     * ./vendor/bin/dewdrop my-command --argument-name value
     * ./vendor/bin/dewdrop my-command --argument-alias=value
     * ./vendor/bin/dewdrop my-command -argument-alias=value
     * ./vendor/bin/dewdrop my-command -argument-alias value
     *
     * In short, you can use one or two dashes at the beginning of the argument
     * name and you can separate the value from the name with either a space
     * or an equals sign.
     *
     * For every argument your command supports, you need to implement a setter
     * method.  For example, if you have an argument with the name "my-argument"
     * then your command class needs a method called "setMyArgument()".
     *
     * Also note that the command API supports the concept of a "primary
     * argument".  See the documentation for the $primaryArgument property for
     * more information about that feature.
     *
     * @param array $args
     *
     * @return boolean Whether args were fully parsed and command can be executed.
     */
    public function parseArgs($args)
    {
        // Which args have been set while parsing
        $argsSet = array();

        foreach ($args as $index => $input) {
            // In this loop, we're only interested in input indicated an argument name
            if (0 !== strpos($input, '-')) {
                continue;
            }

            // If we encounter the --help argument, display command help and stop parsing
            if (0 === stripos($input, '--help')) {
                $this->help();
                return false;
            }

            // Replace any "-" character at beginning of input
            $segment = preg_replace('/^-+/', '', $input);

            if (false !== strpos($segment, '=')) {
                // If there's an equal sign present, our name and value are readily available
                list($name, $value) = explode('=', $segment);

                unset($args[$index]);
            } else {
                // Otherwise, we need to look to the next input segment for the value
                $name  = $segment;
                $next  = $index + 1;

                // The next input segment is only the value if it doesn't start with "-"
                if (isset($args[$next]) && !preg_match('/^-/', $args[$next])) {
                    $value = $args[$next];
                } else {
                    $this->abort('No value given for argument "' . $name . '"');
                    return false;
                }

                unset($args[$index]);
                unset($args[$next]);
            }

            if (0 === strpos($value, '~')) {
                $value = $this->evalPathArgument($value);
            }

            $name     = strtolower($name);
            $selected = false;

            // Now that name and value are available, find matching arg from command definition
            foreach ($this->args as $arg) {
                if ($arg['name'] === $name) {
                    $selected = true;
                }

                foreach ($arg['aliases'] as $alias) {
                    if ($alias === $name) {
                        $selected = true;
                    }
                }

                if ($selected) {
                    $this->setArgValue($arg['name'], $value);

                    $argsSet[] = $arg['name'];
                    break;
                }
            }

            if (!$selected) {
                if (!$this->supportFallbackArgs) {
                    $this->abort('Attempting to set unknown argument "' . $name . '"');
                    return false;
                } else {
                    if (false !== strpos($input, '=')) {
                        $this->fallbackArgs[] = $input;
                    } else {
                        $prefix = '-';

                        if (preg_match('/^--/', $input)) {
                            $prefix = '--';
                        }

                        $this->fallbackArgs[] = $prefix . $name;
                        $this->fallbackArgs[] = $value;
                    }
                }
            }
        }

        // If after matching named args, there is one bit of input left, assign it to our primary arg
        if ($this->primaryArg && !in_array($this->primaryArg, $argsSet) && 1 === count($args)) {
            $this->setArgValue($this->primaryArg, current($args));

            $argsSet[] = $this->primaryArg;
            $input     = array();
        }

        // Ensure no required args were missed
        foreach ($this->args as $arg) {
            if ($arg['required'] && !in_array($arg['name'], $argsSet)) {
                $this->abort('Required argument "' . $arg['name'] . '" not set.');
                return false;
            }
        }

        return true;
    }

    /**
     * Set a human-friendly description of this command's role
     *
     * @param string $description
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * This method is available so the Help command can display a list of
     * available commands.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the primary command that will be used to run this command.  This
     * value differs from aliases because it is displayed in the default help
     * listings.
     *
     * @param string $command
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function setCommand($command)
    {
        $this->command = strtolower($command);

        return $this;
    }

    /**
     * This method is available so the Help command can display a list of
     * available commands.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Register an alias for this command.  This can be useful to provide
     * the user other ways to execute the command.  For example, you might
     * provide a shortened version of the command name so that experienced
     * users can avoid typing the full name once their comfortable with
     * the command.
     *
     * @param string $alias
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function addAlias($alias)
    {
        $this->aliases[] = strtolower($alias);

        return $this;
    }

    /**
     * Add an argument while also setting it as the primary arg for this
     * command.  For more information about the primary arg feature, read
     * the docs on the $primaryArg property.
     *
     * @param string $name
     * @param string $description
     * @param boolean $required
     * @param array $aliases
     *
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function addPrimaryArg($name, $description, $required, $aliases = array())
    {
        $this->primaryArg = $name;

        $this->addArg($name, $description, $required, $aliases);

        return $this;
    }

    /**
     * Register a new argument allowed to be used with this command.
     *
     * @param string $name
     * @param string $description
     * @param boolean $required
     * @param array $aliases
     *
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function addArg($name, $description, $required, $aliases = array())
    {
        $this->args[] = array(
            'name'        => strtolower($name),
            'required'    => $required,
            'description' => $description,
            'aliases'     => array_map('strtolower', $aliases)
        );

        return $this;
    }

    /**
     * Set whether this command supports fallback args.
     *
     * @see $supportFallbackArgs
     * @param boolean $supportFallbackArgs
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function setSupportFallbackArgs($supportFallbackArgs)
    {
        $this->supportFallbackArgs = $supportFallbackArgs;

        return $this;
    }

    /**
     * Add an example usage for this command.  These are displayed in the
     * command's help content.
     *
     * @param string $description
     * @param string $command
     *
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function addExample($description, $command)
    {
        $this->examples[] = array(
            'description' => $description,
            'command'     => $command
        );

        return $this;
    }

    /**
     * Based on the supplied input command, determine whether this command
     * should be selected for argument parsing and execution.
     *
     * @param string $inputCommand
     *
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    public function isSelected($inputCommand)
    {
        $inputCommand = strtolower($inputCommand);

        if ($inputCommand === $this->command) {
            return true;
        }

        foreach ($this->aliases as $alias) {
            if ($alias === $inputCommand) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display help content for this command.
     *
     * The basic command name and description, any avaialble aliases, and
     * any avaialble examples are all included in the help display.
     *
     * This content can be accessed by called "--help" on this command
     * directly:
     *
     * ./vendor/bin/dewdrop my-command --help
     *
     * Or, you can use the built-in help command to access it:
     *
     * ./vendor/bin/dewdrop help my-command
     *
     * @return void
     */
    public function help()
    {
        $this->renderer
            ->title($this->getCommand())
            ->text($this->getDescription());

        if (count($this->aliases)) {
            $this->renderer->text('Aliases: ' . implode(', ', $this->aliases));
        }

        $this->renderer->newline();

        if (count($this->examples)) {
            $this->renderer->subhead('Examples');

            foreach ($this->examples as $example) {
                $this->renderer
                    ->text(rtrim($example['description'], ':') . ':')
                    ->text('    ' . $example['command'])
                    ->newline();
            }
        }

        if (count($this->args)) {
            $this->renderer->subhead('Arguments');

            $rows = array();

            foreach ($this->args as $arg) {
                $title = '--' . $arg['name'];

                $rows[$title] = sprintf(
                    '%s (%s)',
                    $arg['description'],
                    ($arg['required'] ? 'Required' : 'Optional')
                );
            }

            $this->renderer->table($rows);
        }

        return $this;
    }

    /**
     * Get a string representing all the args that were not recognized directly
     * by this command and should be passed along to the underlying/wrapped
     * command.
     *
     * @return string
     */
    protected function getFallbackArgString()
    {
        return implode(' ', $this->fallbackArgs);
    }

    /**
     * Render the provided error message and display the command's help content.
     *
     * @param string $errorMessage
     * @param boolean $displayHelp
     *
     * @return boolean
     */
    protected function abort($errorMessage, $displayHelp = true)
    {
        $this->renderer->error($errorMessage);

        if ($displayHelp) {
            $this->help();
        } else {
            $this->renderer->newline();
        }

        return false;
    }

    /**
     * Use the built-in passthru() function to call an external command and
     * return its exit status.  This is separated into its own method primarily
     * to make it easier to mock during testing.
     *
     * @param string $command
     * @return integer
     */
    protected function passthru($command)
    {
        passthru($command, $exitStatus);

        return (int) $exitStatus;
    }

    /**
     * Run a shell command using PHP's exec fucntion, which may be preferable to
     * passthru() when you need to capture output.  This is primarily present
     * to make it easy to mock exec() during testing.
     *
     * @param string $command
     * @param array $output
     * @param integer $exitStatus
     * @return void
     */
    protected function exec($command, &$output, &$exitStatus)
    {
        exec($command, $output, $exitStatus);
    }

    /**
     * Change "~" prefix to the user's home folder.
     *
     * Bash doesn't do "~" evaluation automatically for command arguments, so
     * we do it here to avoid confusing developers by creating a "~" folder
     * in their WP install instead.
     *
     * @param string $path
     * @return string
     */
    protected function evalPathArgument($path)
    {
        if (0 === strpos($path, '~') && isset($_SERVER['HOME'])) {
            $path = $_SERVER['HOME'] . substr($path, 1);
        }

        return $path;
    }

    /**
     * First, we check to see if the executable is present in Composer's
     * "./vendor/bin/" folder.  Then, we attempt to locate the named
     * executable using "which", if it is available.  Otherwise, just
     * return the name and hope it is in the user's $PATH.
     *
     * @param string $name
     * @return string
     */
    protected function autoDetectExecutable($name)
    {
        $composerPath = getcwd() . '/vendor/bin/' . $name;

        if (file_exists($composerPath)) {
            return $composerPath;
        } elseif (!file_exists('/usr/bin/which')) {
            return $name;
        } else {
            return trim(shell_exec("which {$name}")) ?: $name;
        }
    }

    /**
     * Set the valid of the specified argument.
     *
     * The argument's name is inflected to form a setter name that will be
     * called to set the value.  If no setter is available, execution will
     * be aborted.
     *
     * @param string $name
     * @param string $value
     *
     * @return \Dewdrop\Cli\Command\CommandAbstract
     */
    private function setArgValue($name, $value)
    {
        $words  = explode('-', $name);
        $setter = 'set' . implode('', array_map('ucfirst', $words));

        if (!method_exists($this, $setter)) {
            $this->abort('No setter method available for argument "' . $name . '"');
            return;
        }

        $this->$setter($value);

        return $this;
    }
}
