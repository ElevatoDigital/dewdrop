<?php

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\Mock as MockRenderer;

class Dewdrop_Cli_RunTest extends PHPUnit_Framework_TestCase
{
    public function testConnectDb()
    {
        $runner = new \Dewdrop\Cli\Run();

        $this->assertTrue($runner->connectDb() instanceof \Dewdrop\Db\Adapter);
    }

    public function testUnknownCommand()
    {
        $runner = $this->getMockRunner(array(), 'fafafafafafafafa');

        $runner
            ->expects($this->once())
            ->method('halt');

        $runner
            ->expects($this->once())
            ->method('executeCommand')
            ->with('Help');

        $runner->run();
    }

    public function testRunKnownCommand()
    {
        $runner = $this->getMockRunner(array('--help'), 'gen-admin-component');

        $runner
            ->expects($this->once())
            ->method('halt');

        $runner
            ->expects($this->once())
            ->method('executeCommand')
            ->with('GenAdminComponent');

        $runner->run();
    }

    public function testCommandAutoDetection()
    {
        if (isset($_SERVER['argv'][1])) {
            $originalArg = $_SERVER['argv'][1];
        }

        $_SERVER['argv'][1] = 'gen-admin-component';

        $runner = $this->getMockRunner(array('--help'), null);

        $runner
            ->expects($this->once())
            ->method('halt');

        $runner
            ->expects($this->once())
            ->method('executeCommand')
            ->with('GenAdminComponent');

        $runner->run();

        if (isset($originalArg)) {
            $_SERVER['argv'][1] = $originalArg;
        }
    }

    public function testExecuteCommand()
    {
        $renderer = new MockRenderer();

        $runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'help', $renderer)
        );

        $runner->run();

        $this->assertTrue($renderer->hasOutput('available commands'));
    }

    protected function getMockRunner(array $args, $command)
    {
        $renderer = new MockRenderer();

        $runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt', 'executeCommand'),
            array($args, $command, $renderer)
        );

        return $runner;
    }
}
