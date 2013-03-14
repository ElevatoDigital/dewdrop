<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\RendererInterface;

/**
 * The abstract class used by all CLI commands.
 *
 * This abstract class supplies CLI commands with argument parsing, help
 * content display, alias support, etc.
 *
 * @package Dewdrop
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
     * The command name that should be used on the CLI to select this
     * command class for execution.  For example, when running the
     * dewdrop CLI tool like this:
     *
     * ./dewdrop command-name
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
     * ./dewdrop my-command --folder=example "Example Name Value"
     *
     * The argument parser would set the name argument's value to "Example Name
     * Value" because that is the value expression not explicitly assigned to
     * another argument name.
     *
     * Users can still explicitly set the argument name for the primary argument,
     * too, if they prefer:
     *
     * ./dewdrop --name="Example Name Value"
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
     * Examples of valid usage for this command.
     *
     * @var array
     */
    private $examples = array();

    /**
     * @param \Dewdrop\Cli\Run
     * @param \Dewdrop\Cli\Renderer\RendererInterface
     */
    public function __construct(Run $runner, RendererInterface $renderer)
    {
        $this->runner   = $runner;
        $this->renderer = $renderer;

        $this->addArg(
            'help',
            'Display the help message for this command',
            self::ARG_OPTIONAL
        );

        $this->init();
    }

    /**
     * Implement the init() method in your command sub-class to set required
     * properties.  You'll likely call:
     *
     * - setCommand()
     * - setDescription()
     * - addAlias()
     * - addArg()
     * - setPrimaryArg()
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

    public function parseArgs($input)
    {
        $argsSet = array();

        foreach ($input as $index => $segment) {
            if (0 !== strpos($segment, '-')) {
                continue;
            }

            if (0 === stripos($segment, '--help')) {
                $this->help();
                return false;
            }

            $segment = preg_replace('/^-+/', '', $segment);

            if (false !== strpos($segment, '=')) {
                list($name, $value) = explode('=', $segment);

                unset($input[$index]);
            } else {
                $name  = $segment;
                $next  = $index + 1;

                if (isset($input[$next]) && !preg_match('/^-/', $input[$next])) {
                    $value = $input[$next];
                } else {
                    $this->abort('No value given for argument "' . $name . '"');
                    return false;
                }

                unset($input[$index]);
                unset($input[$next]);
            }

            $name     = strtolower($name);
            $selected = false;

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
                $this->abort('Attempting to set unknown argument "' . $name . '"');
                return false;
            }
        }

        if ($this->primaryArg && !in_array($this->primaryArg, $argsSet) && 1 === count($input)) {
            $this->setArgValue($this->primaryArg, current($input));

            $argsSet[] = $this->primaryArg;
            $input     = array();
        }

        foreach ($this->args as $arg) {
            if ($arg['required'] && !in_array($arg['name'], $argsSet)) {
                $this->abort('Required argument "' . $arg['name'] . '" not set.');
                return false;
            }
        }

        return true;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setCommand($command)
    {
        $this->command = strtolower($command);

        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function addAlias($alias)
    {
        $this->aliases[] = strtolower($alias);

        return $this;
    }

    public function addPrimaryArg($name, $description, $required, $aliases = array())
    {
        $this->primaryArg = $name;

        $this->addArg($name, $description, $required, $aliases);

        return $this;
    }

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

    public function addExample($description, $command)
    {
        $this->examples[] = array(
            'description' => $description,
            'command'     => $command
        );
    }

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

    private function setArgValue($name, $value)
    {
        $words  = explode('-', $name);
        $setter = 'set' . implode('', array_map('ucfirst', $words));

        if (!method_exists($this, $setter)) {
            $this->abort('Attempting to set unknown argument "' . $name . '"');
            return;
        }

        $this->$setter($value);
    }

    protected function abort($errorMessage)
    {
        $this->renderer->error($errorMessage);
        $this->help();
        return $this;
    }
}
