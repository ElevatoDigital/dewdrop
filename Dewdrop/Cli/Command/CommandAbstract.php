<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\RendererInterface;

abstract class CommandAbstract
{
    const ARG_REQUIRED = true;

    const ARG_OPTIONAL = false;

    protected $runner;

    protected $renderer;

    private $command;

    private $description;

    private $aliases = array();

    private $primaryArg;

    private $args = array();

    private $examples = array();

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

    abstract public function init();

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
