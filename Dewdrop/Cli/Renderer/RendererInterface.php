<?php

namespace Dewdrop\Cli\Renderer;

interface RendererInterface
{
    public function title($title);

    public function subhead($subhead);

    public function text($text);

    public function table(array $rows);

    public function error($error);

    public function newline();
}
