<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Run;

abstract class CommandAbstract
{
    const ARG_REQUIRED = true;

    const ARG_OPTIONAL = false;

    protected $runner;

    private $command;

    private $description;

    private $aliases = array();

    private $primaryArg;

    private $args = array();

    private $examples = array();

    public function __construct(Run $runner)
    {
        $this->runner = $runner;

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
                exit;
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
            }
        }

        return $this;
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
        echo PHP_EOL;
        echo 'Help' . PHP_EOL;
        echo '====' . PHP_EOL;
        echo PHP_EOL;

        echo 'Command: ' . $this->getCommand() . PHP_EOL;
        echo 'Description: ' . $this->getDescription() . PHP_EOL;

        if (count($this->aliases)) {
            echo 'Aliases: ' . implode(', ', $this->aliases) . PHP_EOL;
        }

        echo PHP_EOL;

        if (count($this->examples)) {
            echo 'Examples' . PHP_EOL;
            echo '--------' . PHP_EOL;
            echo PHP_EOL;

            foreach ($this->examples as $example) {
                echo rtrim($example['description'], ':') . ':' . PHP_EOL;
                echo '    ' . $example['command'] . PHP_EOL;
                echo PHP_EOL;
            }
        }

        if (count($this->args)) {
            $longestArg = 0;

            foreach ($this->args as $arg) {
                if (strlen($arg['name']) > $longestArg) {
                    $longestArg = strlen($arg['name']);
                }
            }

            echo 'Arguments' . PHP_EOL;
            echo '---------' . PHP_EOL;
            echo PHP_EOL;

            foreach ($this->args as $arg) {
                printf(
                    '--%-' . ($longestArg + 1) . 's %s (%s)' . PHP_EOL,
                    $arg['name'] . ':',
                    $arg['description'],
                    ($arg['required'] ? 'Required' : 'Optional')
                );
            }
        }

        echo PHP_EOL;
    }

    private function setArgValue($name, $value)
    {
        $words  = explode('-', $name);
        $setter = 'set' . implode('', array_map('ucfirst', $words));

        if (!method_exists($this, $setter)) {
            $this->abort('Attempting to set unknown argument "' . $name . '"');
        }

        $this->$setter($value);
    }

    protected function abort($errorMessage)
    {
        echo 'ERROR: ' . $errorMessage . PHP_EOL;

        $this->help();
        exit;
    }
}
