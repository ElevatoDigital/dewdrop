<?php

namespace Dewdrop\Cli\Renderer;

class Markdown implements RendererInterface
{
    public function title($title)
    {
        echo PHP_EOL;

        echo $title . PHP_EOL;
        echo str_repeat('=', strlen($title)) . PHP_EOL;
        echo PHP_EOL;

        return $this;
    }

    public function subhead($subhead)
    {
        echo $subhead . PHP_EOL;
        echo str_repeat('-', strlen($subhead)) . PHP_EOL;
        echo PHP_EOL;

        return $this;
    }

    public function text($text)
    {
        echo wordwrap($text, 80) . PHP_EOL;

        return $this;
    }

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
            printf(
                '%-' . ($longest + 1) . 's %s',
                $title . ':',
                $description
            );

            echo PHP_EOL;
        }

        echo PHP_EOL;

        return $this;
    }

    public function error($error)
    {
        echo PHP_EOL;
        echo 'ERROR: ' . $error . PHP_EOL;

        return $this;
    }

    public function newline()
    {
        echo PHP_EOL;

        return $this;
    }
}
