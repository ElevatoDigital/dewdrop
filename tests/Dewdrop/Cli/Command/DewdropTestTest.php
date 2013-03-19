<?php

namespace Dewdrop\Cli\Command;

class DewdropTestTest extends \PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    protected $command;

    public function setUp()
    {
        $this->renderer = new \Dewdrop\Cli\Renderer\Mock();

        $this->runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'dewdrop-test', $this->renderer)
        );

        $this->command = $this->getMock(
            '\Dewdrop\Cli\Command\DewdropTest',
            array('passthru', 'autoDetectExecutable'),
            array($this->runner, $this->renderer)
        );
    }

    public function testAutoDetectExecutable()
    {
        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('phpunit')
            ->will($this->returnValue('fafafafa'));

        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('fafafafa', null)));

        $this->command->parseArgs(array());
        $this->command->execute();
    }

    public function testWithCodeCoverageAndSpacesInPath()
    {
        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('phpunit')
            ->will($this->returnValue('fafafafa'));

        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('fafafafa', 'My Folder Has Spaces')));

        $this->command->parseArgs(array('--coverage-html="My Folder Has Spaces"'));
        $this->command->execute();
    }

    public function testHomeFolderExpansionInCodeCoverage()
    {
        $originalHome = (isset($_SERVER['HOME']) ? $_SERVER['HOME'] : null);

        $_SERVER['HOME'] = 'TESTHOME';

        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('phpunit')
            ->will($this->returnValue('fafafafa'));

        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('fafafafa', $_SERVER['HOME'] . '/coverage')));

        $this->command->parseArgs(array('--coverage-html=~/coverage'));
        $this->command->execute();

        if (null !== $originalHome) {
            $_SERVER['HOME'] = $originalHome;
        }
    }

    public function testManuallySetPhpunitArgument()
    {
        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue($this->assembleCommand('manualphpunit', null)));

        $this->command->parseArgs(array('--phpunit=manualphpunit'));
        $this->command->execute();
    }

    protected function assembleCommand($bin, $coverage)
    {
        $path = realpath(__DIR__ . '/../../../');

        $cmd  = $bin;
        $cmd .= ' -c ' . escapeshellarg($path . '/phpunit.xml');

        if ($coverage) {
            $cmd .= ' --coverage-html=' . escapeshellarg($coverage);
        }

        $cmd .= ' ' . escapeshellarg($path);

        return $cmd;
    }
}
