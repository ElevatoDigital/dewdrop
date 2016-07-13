<?php

namespace Dewdrop\Cli\Renderer;

use Zend\Console\Adapter\AbstractAdapter;

/**
 * This is an adapter for \Zend\Console that lets us inject some content
 * to use instead of reading from php://stdin.  Allows us to test prompt
 * methods.
 */
class MockStdinConsole extends AbstractAdapter
{
    /**
     * @var string
     */
    private $mockStdinContent;

    private $readCharPosition = 0;

    /**
     * @param $mockStdinContent
     * @return $this
     */
    public function setMockStdinContent($mockStdinContent)
    {
        $this->mockStdinContent = $mockStdinContent;

        return $this;
    }

    /**
     * Read a single line from the console input
     *
     * @param int $maxLength        Maximum response length
     * @return string
     */
    public function readLine($maxLength = 2048)
    {
        return rtrim($this->mockStdinContent, "\n\r");
    }

    public function readChar($mask = null)
    {
        $char = substr($this->mockStdinContent, $this->readCharPosition, 1);
        $this->readCharPosition += 1;
        return $char;
    }

    /**
     * @param string $text
     * @param null $color
     * @param null $bgColor
     */
    public function write($text, $color = null, $bgColor = null)
    {
        // No-op during testing
    }

    public function writeAt($text, $x, $y, $color = null, $bgColor = null)
    {
        // No-op during testing
    }

    public function writeText($text, $color = null, $bgColor = null)
    {
        // No-op during testing
    }

    /**
     * Clear line at cursor position
     */
    public function clearLine()
    {
        // No-op during testing
    }
}
