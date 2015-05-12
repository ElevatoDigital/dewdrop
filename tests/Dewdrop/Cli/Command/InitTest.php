<?php

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\Mock as MockRenderer;

class Dewdrop_Cli_Command_InitTest extends PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    public function setUp()
    {
        $this->renderer = new MockRenderer();
        $this->runner   = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'init', $this->renderer)
        );

        $this->command = $this->getMock(
            '\Dewdrop\Cli\Command\Init',
            array('commandShouldExecute'),
            array($this->runner, $this->renderer)
        );
    }

    public function testShouldNotExecuteInWpEnvironment()
    {
        $init = $this->getMock(
            '\Dewdrop\Cli\Command\Init',
            array('abort'),
            array($this->runner, $this->renderer)
        );

        $init
            ->expects($this->once())
            ->method('abort');

        $init
            ->parseArgs(array());
        $init->execute();
    }

    public function testShouldAbortWithMessage()
    {
        $init = $this->getMock(
            '\Dewdrop\Cli\Command\Init',
            array('abort'),
            array($this->runner, $this->renderer)
        );

        $init
            ->expects($this->once())
            ->method('abort');

        $init
            ->parseArgs(array());

        $init->execute();

        $this->assertTrue($this->renderer->hasOutput('Should not execute'));
    }
}
