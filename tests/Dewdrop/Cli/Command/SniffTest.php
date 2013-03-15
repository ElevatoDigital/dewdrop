<?php

namespace Dewdrop\Cli\Command;

class SniffTest extends \PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    protected $command;

    public function setUp()
    {
        $this->renderer = new \Dewdrop\Cli\Renderer\Mock();

        $this->runner = new \Dewdrop\Cli\Run(
            array(),
            'sniff',
            $this->renderer
        );

        $this->command = $this->getMock(
            '\Dewdrop\Cli\Command\Sniff',
            array('passthru', 'autoDetectExecutable'),
            array($this->runner, $this->renderer)
        );
    }

    public function testAutoDetectExecutable()
    {
        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('phpcs')
            ->will($this->returnValue('fafafafa'));

        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('fafafafa', null)));

        $this->command->parseArgs(array());
        $this->command->execute();
    }

    public function testCustomStandardArg()
    {
        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('phpcs')
            ->will($this->returnValue('fafafafa'));

        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('fafafafa', 'Zend')));

        $this->command->parseArgs(array('--standard=Zend'));
        $this->command->execute();
    }

    public function testCustomPhpcsExecutable()
    {
        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('fafafa')));

        $this->command->parseArgs(array('--phpcs=fafafa'));
        $this->command->execute();
    }

    protected function assembleCommand($bin, $standard = 'PSR2')
    {
        $path = realpath(__DIR__ . '/../../../');

        $cmd  = $bin;
        $cmd .= ' --standard=' . $standard;
        $cmd .= ' --ignore=*/Zend/* --ignore=*/tests/* --ignore=*/models/metadata/* ';
        $cmd .= escapeshellarg($path);

        return $cmd;
    }
}
