<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\PageFactory;

use ReflectionClass;

/**
 * This class is a simple value object for pages returned from
 * PageFactoryInterface->listAllPages() methods to ensure they are consistently
 * handled in the various factories.
 */
class Page
{
    /**
     * The name of the page in URL routes.
     *
     * @var string
     */
    private $name;

    /**
     * The file where the page class is defined.
     *
     * @var string
     */
    private $file;

    /**
     * The name of the page class.
     *
     * @var string
     */
    private $className;

    /**
     * Provide information about the page provided by the factory.
     *
     * @param string $name
     * @param string $file
     * @param string $className
     */
    public function __construct($name, $file, $className)
    {
        $this->name      = $name;
        $this->file      = $file;
        $this->className = $className;
    }

    /**
     * Get the name of the page used to route to it in the URL.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the name of the file where the page is defined.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get the name of the class the page is defined in.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Use reflection to grab the docblock off the page class and trim out the
     * various comment formatting noise.
     *
     * @return string
     */
    public function getDocBlock()
    {
        require_once $this->file;
        $reflection = new ReflectionClass($this->className);
        $docComment = $reflection->getDocComment();

        $lines = explode(PHP_EOL, trim($docComment, '/'));

        foreach ($lines as $index => $line) {
            $lines[$index] = trim($line, ' *');
        }

        return implode(' ', $lines);
    }
}
