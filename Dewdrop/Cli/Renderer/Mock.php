<?php

namespace Dewdrop\Cli\Renderer;

class Mock implements RendererInterface
{
    protected $output = '';

    public function title($title)
    {
        $this->output .= $title;

        return $this;
    }

    public function subhead($subhead)
    {
        $this->output .= $subhead;

        return $this;
    }

    public function text($text)
    {
        $this->output .= $text;

        return $this;
    }

    public function table(array $rows)
    {
        foreach ($rows as $title => $description) {
            $this->output .= $title;
            $this->output .= $description;
        }

        return $this;
    }

    public function success($message)
    {
        $this->output .= $message;

        return $this;
    }

    public function warn($warning)
    {
        $this->output .= $warning;

        return $this;
    }

    public function error($error)
    {
        $this->output .= $error;

        return $this;
    }

    public function newline()
    {

    }

    public function unorderedList(array $items)
    {
        foreach ($items as $item) {
            $this->output .= $item;
        }

        return $this;
    }

    public function hasOutput($search)
    {
        return false !== stripos($this->output, $search);
    }
}
