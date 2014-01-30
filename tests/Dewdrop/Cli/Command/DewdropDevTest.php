<?php

namespace Dewdrop\Cli\Command;

class DewdropDevTest extends \PHPUnit_Framework_TestCase
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
            array(array(), 'dewdrop-dev', $this->renderer)
        );

        $this->command = $this->getMock(
            '\Dewdrop\Cli\Command\DewdropDev',
            array(
                'abort',
                'autoDetectExecutable',
                'exec',
                'gitCloneAlreadyPresent',
                'moveDewdropInstallation'
            ),
            array($this->runner, $this->renderer)
        );
    }

    public function testAutoDetectExecutable()
    {
        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('git')
            ->will($this->returnValue('fafafafa'));

        $this->command
            ->expects($this->once())
            ->method('gitCloneAlreadyPresent')
            ->will($this->returnValue(false));

        $this->command
            ->expects($this->once())
            ->method('moveDewdropInstallation');

        $this->command
            ->expects($this->once())
            ->method('exec');

        $this->command->parseArgs(array());
        $this->command->execute();
    }
}
