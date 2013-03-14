<?php

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\Mock as MockRenderer;

class Dewdrop_Cli_Command_HelpTest extends PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    public function setUp()
    {
        $this->renderer = new MockRenderer();
        $this->runner   = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'help', $this->renderer)
        );
    }

    public function testDisplayGlobalHelp()
    {
        $help = $this->getMock(
            '\Dewdrop\Cli\Command\Help',
            array('displayGlobalHelp'),
            array($this->runner, $this->renderer)
        );

        $help
            ->expects($this->once())
            ->method('displayGlobalHelp');

        $help->parseArgs(array());
        $help->execute();
    }

    public function testHelpArg()
    {
        $help = $this->getMock(
            '\Dewdrop\Cli\Command\Help',
            array('help'),
            array($this->runner, $this->renderer)
        );

        $help
            ->expects($this->once())
            ->method('help');

        $this->assertFalse($help->parseArgs(array('--help')));
    }

    public function testSubcommandArgWithKnownCommand()
    {
        $this->runner
            ->setArgs(array('--subcommand=gen-admin-component'))
            ->run();

        $this->assertTrue($this->renderer->hasOutput('gen-admin-component'));
        $this->assertFalse($this->renderer->hasOutput('Available Commands'));
    }

    public function testSubcommandArgWithKnownCommandAndNoEqualsSign()
    {
        $this->runner
            ->setArgs(array('--subcommand', 'gen-admin-component'))
            ->run();

        $this->assertTrue($this->renderer->hasOutput('gen-admin-component'));
        $this->assertFalse($this->renderer->hasOutput('Available Commands'));
    }

    public function testSubcommandArgWithKnownCommandAndNoValue()
    {
        $this->runner
            ->setArgs(array('--subcommand'))
            ->run();

        $this->assertTrue($this->renderer->hasOutput('no value given'));
    }

    public function testUnknownArgumentProvided()
    {
        $help = $this->getMock(
            '\Dewdrop\Cli\Command\Help',
            array('help'),
            array($this->runner, $this->renderer)
        );

        $help
            ->expects($this->once())
            ->method('help');

        $help->parseArgs(array('--fafafafa=1'));

        $this->assertTrue($this->renderer->hasOutput('unknown argument'));
    }

    public function testSubcommandArgWithUnknownCommand()
    {
        $this->runner
            ->setArgs(array('--subcommand=fafafafa'))
            ->run();

        $this->assertTrue($this->renderer->hasOutput('Available Commands'));
        $this->assertFalse($this->renderer->hasOutput('ERROR'));
    }
}
