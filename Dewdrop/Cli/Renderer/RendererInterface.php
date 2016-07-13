<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Renderer;

/**
 * The interface CLI renderers must implement.  Markdown is used by
 * default, but alternate renders could be used to render directly to
 * HTML, etc.
 */
interface RendererInterface
{
    /**
     * Display the primary title for the output.
     *
     * @param string $title
     * @returns RendererInterface
     */
    public function title($title);

    /**
     * Display a subhead, or 2nd-level header.
     *
     * @param string $subhead
     * @returns RendererInterface
     */
    public function subhead($subhead);

    /**
     * Display a single line or block of text.
     *
     * @param string $text
     * @returns RendererInterface
     */
    public function text($text);

    /**
     * Display a table.  The supplied array should have the row title as
     * the keys and the descriptions as the array values.
     *
     * @param array $rows
     * @returns RendererInterface
     */
    public function table(array $rows);

    /**
     * Display a success message.
     *
     * @param string $message
     * @returns RendererInterface
     */
    public function success($message);

    /**
     * Display a warning message.
     *
     * @param string $warning
     * @returns RendererInterface
     */
    public function warn($warning);

    /**
     * Display an error message.
     *
     * @param string $error
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

    /**
     * Ask the user for a line of input.
     *
     * @param $promptText
     * @param bool $allowEmpty
     * @return string
     */
    public function ask($promptText, $allowEmpty = false);

    /**
     * Ask the user for a line of input and don't display that input as they're
     * typing.  Ideal for passwords, etc.
     *
     * @param $promptText
     * @param bool $allowEmpty
     * @return string
     */
    public function secret($promptText, $allowEmpty = false);

    /**
     * Request that the user select an option from a list.
     *
     * @param $promptText
     * @param array $options
     * @param bool $allowEmpty
     * @return string
     */
    public function select($promptText, array $options, $allowEmpty = false);

    /**
     * Ask the user to answer a yes/no confirmation prompt.
     *
     * @param $promptText
     * @return boolean
     */
    public function confirm($promptText);
}
