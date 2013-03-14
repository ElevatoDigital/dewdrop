<?php

namespace Dewdrop\Cli\Command;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    protected $command;

    public function setUp()
    {
        $this->renderer = new \Dewdrop\Cli\Renderer\Mock();

        $this->runner = new \Dewdrop\Cli\Run(
            array(),
            'dewdrop-test',
            $this->renderer
        );

        $this->command = $this->getMock(
            '\Dewdrop\Cli\Command\Update',
            array('passthru', 'autoDetectExecutable'),
            array($this->runner, $this->renderer)
        );
    }

    public function testNormalExecution()
    {
        $this->command
            ->expects($this->once())
            ->method('autoDetectExecutable')
            ->with('git')
            ->will($this->returnValue('git'));

        $this->command
            ->expects($this->once())
            ->method('passthru')
            ->will($this->returnValue('git pull'));

        $this->command->parseArgs(array());
        $this->command->execute();
    }
}
