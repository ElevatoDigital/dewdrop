<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Renderer\Mock as MockRenderer;

class GenDbTableTest extends \PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    public function setUp()
    {
        $this->renderer = new MockRenderer();

        $this->runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'gen-admin-component', $this->renderer)
        );
    }

    public function testAbortsWhenModelAlreadyExists()
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\GenDbTable',
            array('modelAlreadyExists'),
            array($this->runner, $this->renderer)
        );

        $command
            ->expects($this->once())
            ->method('modelAlreadyExists')
            ->will($this->returnValue(true));

        $command->parseArgs(array('fafafafa'));
        $command->execute();
    }

    public function testAbortsWhenDbdeployFileAlreadyExists()
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\GenDbTable',
            array('dbdeployFileAlreadyExists'),
            array($this->runner, $this->renderer)
        );

        $command
            ->expects($this->once())
            ->method('dbdeployFileAlreadyExists')
            ->will($this->returnValue(true));

        $command->parseArgs(array('fafafafa'));
        $command->execute();
    }

    public function testNotSettingModelClassWillInflectFromTableName()
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\GenDbTable',
            array('modelAlreadyExists'),
            array($this->runner, $this->renderer)
        );

        $command
            ->expects($this->once())
            ->method('modelAlreadyExists')
            ->with(new \PHPUnit_Framework_Constraint_StringContains('Fafafafa'))
            ->will($this->returnValue(true));

        $command->parseArgs(array('fafafafa'));
        $command->execute();
    }

    public function testSetModelClassNameManuallyDoesNotInflect()
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\GenDbTable',
            array('modelAlreadyExists'),
            array($this->runner, $this->renderer)
        );

        $command
            ->expects($this->once())
            ->method('modelAlreadyExists')
            ->with(new \PHPUnit_Framework_Constraint_StringContains('asdfasdfasdf'))
            ->will($this->returnValue(true));

        $command->parseArgs(array('fafafafa', '--model-class=asdfasdfasdf'));
        $command->execute();
    }

    public function testValidArgumentsWillWriteTwoTemplates()
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\GenDbTable',
            array('writeFile'),
            array($this->runner, $this->renderer)
        );

        $command
            ->expects($this->at(0))
            ->method('writeFile')
            ->with(new \PHPUnit_Framework_Constraint_StringContains('Fafafafas.php'))
            ->will($this->returnValue(true));

        $command
            ->expects($this->at(1))
            ->method('writeFile')
            ->with(new \PHPUnit_Framework_Constraint_StringContains('fafafafa.sql'))
            ->will($this->returnValue(true));

        $command->parseArgs(array('fafafafa'));
        $command->execute();
    }
}
