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
 * This class just stores everything passed to the renderer so that it can
 * later be checked for certain patterns in order to test CLI commands.
 */
class Mock implements RendererInterface
{
    /**
     * Output buffer used to allow testing of output during testing.
     *
     * @var string
     */
    protected $output = '';

    /**
     * Display the primary title for the output.
     *
     * @param string $title
     * @returns RendererInterface
     */
    public function title($title)
    {
        $this->output .= $title;

        return $this;
    }

    /**
     * Display a subhead, or 2nd-level header.
     *
     * @param string $subhead
     * @returns RendererInterface
     */
    public function subhead($subhead)
    {
        $this->output .= $subhead;

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
        $this->output .= $text;

        return $this;
    }

    /**
     * Display a table.  The supplied array should have the row title as
     * the keys and the descriptions as the array values.
     *
     * @param array $rows
     * @returns RendererInterface
     */
    public function table(array $rows)
    {
        foreach ($rows as $title => $description) {
            $this->output .= $title;
            $this->output .= $description;
        }

        return $this;
    }

    /**
     * Display a success message.
     *
     * @param string $message
     * @returns RendererInterface
     */
    public function success($message)
    {
        $this->output .= $message;

        return $this;
    }

    /**
     * Display a warning message.
     *
     * @param string $warning
     * @returns RendererInterface
     */
    public function warn($warning)
    {
        $this->output .= $warning;

        return $this;
    }

    /**
     * Display an error message.
     *
     * @param string $error
     * @returns RendererInterface
     */
    public function error($error)
    {
        $this->output .= $error;

        return $this;
    }

    /**
     * Display a newline/line break.
     *
     * @returns RendererInterface
     */
    public function newline()
    {
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
            $this->output .= $item;
        }

        return $this;
    }

    /**
     * Check whether the provided search string is present anywhere in the
     * output buffer of this renderer.  This can be useful for testing CLI
     * commands.
     *
     * @param string $search
     * @return boolean
     */
    public function hasOutput($search)
    {
        return false !== stripos($this->output, $search);
    }

    /**
     * Ask the user for a line of input.
     *
     * @param $promptText
     * @param bool $allowEmpty
     * @return string
     */
    public function ask($promptText, $allowEmpty = false)
    {
    }

    /**
     * Ask the user for a line of input and don't display that input as they're
     * typing.  Ideal for passwords, etc.
     *
     * @param $promptText
     * @param bool $allowEmpty
     * @return string
     */
    public function secret($promptText, $allowEmpty = false)
    {
    }

    /**
     * Request that the user select an option from a list.
     *
     * @param $promptText
     * @param array $options
     * @param bool $allowEmpty
     * @return string
     */
    public function select($promptText, array $options, $allowEmpty = false)
    {
    }

    /**
     * Ask the user to answer a yes/no confirmation prompt.
     *
     * @param $promptText
     * @return boolean
     */
    public function confirm($promptText)
    {
    }
}
