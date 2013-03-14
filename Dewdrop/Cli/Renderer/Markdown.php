<?php

namespace Dewdrop\Cli\Renderer;

/**
 * Render output as Markdown.  For more information, see:
 *
 * <http://daringfireball.net/projects/markdown/>
 *
 * @package Dewdrop
 */
class Markdown implements RendererInterface
{
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
    public function error($error)
    {
        echo PHP_EOL;
        echo 'ERROR: ' . $error . PHP_EOL;

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
