<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Renderer\Mock;
use PHPUnit_Framework_TestCase;

class GenerateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Dewdrop\Cli\Renderer\RendererInterface
     */
    private $renderer;

    /**
     * @var \Dewdrop\Cli\Run
     */
    private $runner;

    public function setUp()
    {
        $this->renderer = new Mock();

        $this->runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            ['halt'],
            [[], 'generate', $this->renderer]
        );
    }

    public function testSupplyingNoTemplateNameDisplaysHelper()
    {
        /* @var $command Generate|\PHPUnit_Framework_MockObject_MockObject */
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\Generate',
            ['displayHelp'],
            [$this->runner, $this->renderer]
        );

        $command
            ->expects($this->once())
            ->method('displayHelp');

        $command->execute();
    }
}
