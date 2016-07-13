<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Renderer;

use Zend\Console\Console;
use Zend\Console\Adapter\AbstractAdapter as ConsoleAdapter;
use Zend\Console\Color\Xterm256 as Color;
use Zend\Console\Prompt\Confirm as ConfirmPrompt;
use Zend\Console\Prompt\Line as LinePrompt;
use Zend\Console\Prompt\Password as PasswordPrompt;
use Zend\Console\Prompt\Select as SelectPrompt;

/**
 * Render output as Markdown.  For more information, see:
 *
 * <http://daringfireball.net/projects/markdown/>
 */
class Markdown implements RendererInterface
{
    /**
     * The console adapter used for rendering color output.
     *
     * @var ConsoleAdapter
     */
    private $console;

    /**
     * Create console instance for color output of success/failure
     * messages.
     *
     * @param ConsoleAdapter $console
     */
    public function __construct(ConsoleAdapter $console = null)
    {
        if (null === $console) {
            $this->console = Console::getInstance();
        } else {
            $this->console = $console;
        }
    }

    /**
     * Display the primary title for the output using the "=" Markdown
     * syntax:
     *
     * <pre>
     * Title
     * =====
     * </pre>
     *
     * @param string $title
     * @returns RendererInterface
     */
    public function title($title)
    {
        echo PHP_EOL;

        echo $title . PHP_EOL;
        echo str_repeat('=', strlen($title)) . PHP_EOL;
        echo PHP_EOL;

        return $this;
    }

    /**
     * Display a subhead, or 2nd-level header using the "hyphen" Markdown
     * syntax:
     *
     * <pre>
     * Subhead
     * -------
     * </pre>
     *
     * @param string $subhead
     * @returns RendererInterface
     */
    public function subhead($subhead)
    {
        echo $subhead . PHP_EOL;
        echo str_repeat('-', strlen($subhead)) . PHP_EOL;
        echo PHP_EOL;

        return $this;
    }

    /**
     * Display a single line or block of text.
     *
     * @param string $text
     * @returns RendererInterface
     */
    public function text($text)
    {
        echo wordwrap($text, 80) . PHP_EOL;

        return $this;
    }

    /**
     * Display a table.  The supplied array should have the row title as
     * the keys and the descriptions as the array values.
     *
     * This doesn't actually use any Markdown extenstion syntax for tables,
     * so it might be worth looking into that possibility, if we do plan
     * to transform the Markdown to HTML at some point.
     *
     * @param array $rows
     * @returns RendererInterface
     */
    public function table(array $rows)
    {
        $longest = 0;

        foreach ($rows as $title => $description) {
            $len = strlen($title);

            if ($len > $longest) {
                $longest = $len;
            }
        }

        foreach ($rows as $title => $description) {
            echo str_replace(
                PHP_EOL,
                PHP_EOL . str_repeat(' ', $longest + 2),
                wordwrap(
                    sprintf(
                        '%-' . ($longest + 1) . 's %s',
                        $title . ':',
                        $description
                    ),
                    80
                )
            );

            echo PHP_EOL;
        }

        echo PHP_EOL;

        return $this;
    }

    /**
     * Display a success message using green color on the console to make it
     * easier to spot at a glance.
     *
     * @param string $message
     * @returns RendererInterface
     */
    public function success($message)
    {
        $this->console->writeLine(
            $message . PHP_EOL,
            Color::calculate('00ff00')
        );

        return $this;
    }

    /**
     * Display a warning message using yellow color on the console to make it
     * easier to spot at a glance.
     *
     * @param string $warning
     * @returns RendererInterface
     */
    public function warn($warning)
    {
        $this->console->writeLine(
            $warning . PHP_EOL,
            Color::calculate('ffff00')
        );

        return $this;
    }

    /**
     * Display an error message using red color on the console to make it
     * easier to spot.
     *
     * @param string $error
     * @returns RendererInterface
     */
    public function error($error)
    {
        echo PHP_EOL;

        $this->console->writeLine(
            'ERROR: ' . $error . PHP_EOL,
            Color::calculate('ff0000')
        );

        echo PHP_EOL;

        return $this;
    }

    /**
     * Display an unordered (bulleted) list.
     *
     * @param array $items
     * @return RendererInterface
     */
    public function unorderedList(array $items)
    {
        foreach ($items as $item) {
            $this->text("* {$item}");
        }

        return $this;
    }

    /**
     * Display a newline/line break.
     *
     * @returns RendererInterface
     */
    public function newline()
    {
        echo PHP_EOL;

        return $this;
    }

    public function ask($promptText, $allowEmpty = false)
    {
        $prompt = new LinePrompt($promptText, $allowEmpty);
        $prompt->setConsole($this->console);
        return $prompt->show();
    }

    public function secret($promptText, $allowEmpty = false)
    {
        $prompt = new PasswordPrompt($promptText, $allowEmpty, true);
        $prompt->setConsole($this->console);
        return $prompt->show();
    }

    public function select($promptText, array $options, $allowEmpty = false)
    {
        $prompt = new SelectPrompt($promptText, $options, $allowEmpty);
        $prompt->setConsole($this->console);
        return $prompt->show();
    }

    public function confirm($promptText, $echo = true)
    {
        $prompt = new ConfirmPrompt($promptText);
        $prompt->setEcho($echo);
        $prompt->setConsole($this->console);
        return $prompt->show();
    }
}
