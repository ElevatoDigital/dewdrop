<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

/**
 * Generate API documentation for all Dewdrop libraries with
 * phpdoc (a.k.a. phpDocumentor 2).
 */
class DewdropDoc extends CommandAbstract
{
    /**
     * Where you'd like the phpdoc output to be put
     *
     * @var string
     */
    private $outputDir;

    /**
     * The path to the phpdoc binary
     *
     * @var string
     */
    private $phpdoc;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Generate API docs for the Dewdrop libraries')
            ->setSupportFallbackArgs(true)
            ->setCommand('dewdrop-doc')
            ->addAlias('doc-dewdrop')
            ->addAlias('phpdoc-dewdrop')
            ->addAlias('dewdrop-phpdoc');

        $this->addArg(
            'phpdoc',
            'The path to the phpdoc binary',
            self::ARG_OPTIONAL
        );
    }

    /**
     * Manually set the location of the phpdoc executable
     *
     * @param string $phpdoc
     * @return \Dewdrop\Cli\Command\DewdropDoc
     */
    public function setPhpdoc($phpdoc)
    {
        $this->phpdoc = $phpdoc;

        return $this;
    }

    /**
     * Run phpdoc to generate HTML API docs for the Dewdrop libraries
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->phpdoc) {
            $this->phpdoc = $this->autoDetectExecutable('phpdoc');
        }

        $cmd = sprintf(
            "%s --sourcecode --defaultpackagename=Dewdrop --title=Dewdrop --ignore=*.phtml -d %s %s",
            $this->phpdoc,
            escapeshellarg($this->paths->getDewdropLib() . '/Dewdrop'),
            $this->getFallbackArgString()
        );

        $this->passthru($cmd);
    }
}
