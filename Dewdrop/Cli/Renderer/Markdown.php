<?php

namespace Dewdrop\Cli\Renderer;

use Zend\Console\Console;
use Zend\Console\Color\Xterm256 as Color;

/**
 * Render output as Markdown.  For more information, see:
 *
 * <http://daringfireball.net/projects/markdown/>
 *
 * @package Dewdrop
 */
class Markdown implements RendererInterface
{
    private $console;

    public function __construct()
    {
        $this->console = Console::getInstance();
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function subhead($subhead)
    {
        echo $subhead . PHP_EOL;
        echo str_repeat('-', strlen($subhead)) . PHP_EOL;
        echo PHP_EOL;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function text($text)
    {
        echo wordwrap($text, 80) . PHP_EOL;

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function error($error)
    {
        echo PHP_EOL;

        $this->console->writeLine(
            'ERROR: ' . $error . PHP_EOL,
            Color::calculate('ff0000')
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function unorderedList(array $items)
    {
        foreach ($items as $item) {
            $this->text("* {$item}");
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function newline()
    {
        echo PHP_EOL;

        return $this;
    }
}
