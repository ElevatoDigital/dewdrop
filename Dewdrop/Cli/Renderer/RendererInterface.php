<?php

namespace Dewdrop\Cli\Renderer;

/**
 * The interface CLI renderers must implement.  Markdown is used by
 * default, but alternate renders could be used to render directly to
 * HTML, etc.
 *
 * @package Dewdrop
 */
interface RendererInterface
{
    /**
     * Display the primary title for the output.
     *
     * @param string
     * @returns RendererInterface
     */
    public function title($title);

    /**
     * Display a subhead, or 2nd-level header.
     *
     * @param string
     * @returns RendererInterface
     */
    public function subhead($subhead);

    /**
     * Display a single line or block of text.
     *
     * @param string
     * @returns RendererInterface
     */
    public function text($text);

    /**
     * Display a table.  The supplied array should have the row title as
     * the keys and the descriptions as the array values.
     *
     * @param array
     * @returns RendererInterface
     */
    public function table(array $rows);

    /**
     * Display an error message.
     *
     * @param string
     * @returns RendererInterface
     */
    public function error($error);

    /**
     * Display a newline/line break.
     *
     * @returns RendererInterface
     */
    public function newline();

    /**
     * Display an unordered (bulleted) list.
     *
     * @param array $items
     * @return RendererInterface
     */
    public function unorderedList(array $items);
}
