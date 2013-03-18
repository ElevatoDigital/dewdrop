<?php

namespace Dewdrop\Cli\Command;

class DewdropDoc extends CommandAbstract
{
    private $outputDir;

    private $phpdoc;

    public function init()
    {
        $this
            ->setDescription('Generate API docs for the Dewdrop libraries')
            ->setCommand('dewdrop-doc')
            ->addAlias('doc-dewdrop')
            ->addAlias('phpdoc-dewdrop')
            ->addAlias('dewdrop-phpdoc');

        $this->addArg(
            'output-dir',
            'The folder to generate the docs in',
            self::ARG_REQUIRED,
            array('output', 'o')
        );

        $this->addArg(
            'phpdoc',
            'The path to the phpdoc binary',
            self::ARG_OPTIONAL
        );
    }

    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    public function setPhpdoc($phpdoc)
    {
        $this->phpdoc = $phpdoc;

        return $this;
    }

    public function execute()
    {
        if (!$this->phpdoc) {
            $this->phpdoc = $this->autoDetectExecutable('phpdoc');
        }

        $cmd = sprintf(
            "%s -d %s -t %s",
            $this->phpdoc,
            escapeshellarg($this->paths->getDewdropLib()),
            escapeshellarg($this->evalPathArgument($this->outputDir))
        );

        $this->passthru($cmd);
    }
}
