<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Renderer\Mock as MockRenderer;

class PackageTest extends \PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    protected $command;

    public function setUp()
    {
        $this->renderer = new MockRenderer();

        $this->runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'package', $this->renderer)
        );

        $this->command = $this->getMock(
            '\Dewdrop\Cli\Command\Package',
            array('abort', 'autoDetectExecutable', 'mkdir', 'passthru', 'writeFile'),
            array($this->runner, $this->renderer)
        );
    }

    public function testSetNamespace()
    {
        static $namespace = 'ns';

        $this->assertSame($this->command, $this->command->setNamespace($namespace));
    }

    public function testSetOutputDir()
    {
        static $outputDir = 'out';

        $this->assertSame($this->command, $this->command->setOutputDir($outputDir));
    }

    public function testExecuteWithNoArguments()
    {
        static $args = array();

        $this->command
            ->expects($this->once())
            ->method('abort');

        $this->assertFalse($this->command->parseArgs($args));

        $this->runner
            ->setArgs(array_merge(array('package'), $args))
            ->run();

        $this->assertTrue($this->renderer->hasOutput('Required argument "output-dir" not set.'));
    }

    public function testExecuteWithOutputDirArgumentOnly()
    {
        static $args = array(
            '--output-dir=test-package',
        );

        $this->command
            ->expects($this->once())
            ->method('abort');

        $this->assertFalse($this->command->parseArgs($args));

        $this->runner
            ->setArgs(array_merge(array('package'), $args))
            ->run();

        $this->assertTrue($this->renderer->hasOutput('Required argument "namespace" not set.'));
    }

    public function testExecuteWithValidArguments()
    {
        static $args = array(
            '--output-dir=test-package',
            '--namespace=My',
        );

        $this->assertTrue($this->command->parseArgs($args));

        $this->command
            ->expects($this->once())
            ->method('mkdir');

        $this->command
            ->expects($this->atLeastOnce())
            ->method('writeFile');

        $this->command->execute();
    }
}
