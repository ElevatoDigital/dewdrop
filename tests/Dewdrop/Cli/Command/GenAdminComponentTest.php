<?php

use Dewdrop\Cli\Run;
use Dewdrop\Cli\Renderer\Mock as MockRenderer;

class Dewdrop_Cli_Command_GenAdminComponentTest extends PHPUnit_Framework_TestCase
{
    protected $runner;

    protected $renderer;

    public function setUp()
    {
        $this->renderer = new MockRenderer();
        $this->runner   = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'gen-admin-component', $this->renderer)
        );
    }

    public function testSelectWithCommandAlias()
    {
        $renderer = new MockRenderer();
        $runner   = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt', 'executeCommand'),
            array(array(), 'admin-component', $renderer)
        );

        $runner
            ->expects($this->once())
            ->method('executeCommand')
            ->with('GenAdminComponent');

        $runner->run();
    }

    public function testManuallySetAllArgsWithAliases()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('setTitle', 'setNamespace', 'setFolder'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->once())
            ->method('setTitle')
            ->with('Title');

        $gen
            ->expects($this->once())
            ->method('setFolder')
            ->with('Folder');

        $gen
            ->expects($this->once())
            ->method('setNamespace')
            ->with('Namespace');

        $gen
            ->parseArgs(
                array(
                    '-t',
                    'Title',
                    '-f',
                    'Folder',
                    '-n',
                    'Namespace'
                )
            );
    }

    public function testPrimaryArgComesLastAfterExplicitArgs()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('setTitle', 'setNamespace', 'setFolder'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->once())
            ->method('setTitle')
            ->with('Title');

        $gen
            ->expects($this->once())
            ->method('setFolder')
            ->with('Folder');

        $gen
            ->expects($this->once())
            ->method('setNamespace')
            ->with('Namespace');

        $gen
            ->parseArgs(
                array(
                    '-f',
                    'Folder',
                    '-n',
                    'Namespace',
                    'Title'
                )
            );
    }

    public function testRequiredArgumentWasSkipped()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('abort'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->once())
            ->method('abort');

        $gen
            ->parseArgs(array());
    }

    public function testAbortWhenComponentAlreadyExists()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('componentAlreadyExists', 'abort'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->any())
            ->method('componentAlreadyExists')
            ->will($this->returnValue(true));

        $gen
            ->expects($this->once())
            ->method('abort');

        $gen->parseArgs(array('fafafafa'));
        $gen->execute();
    }

    public function testFolderNameInflectedFromName()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('componentAlreadyExists'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->once())
            ->method('componentAlreadyExists')
            ->with($gen->getComponentPath() . '/fruit-manager')
            ->will($this->returnValue(true));

        $gen->parseArgs(array('"Fruit Manager"'));
        $gen->execute();
    }

    public function testManuallySetAllArgsWithAliasesAndExecute()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('componentAlreadyExists'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->once())
            ->method('componentAlreadyExists')
            ->with($gen->getComponentPath() . '/Folder')
            ->will($this->returnValue(true));

        $gen
            ->parseArgs(
                array(
                    '-t',
                    'Title',
                    '-f',
                    'Folder',
                    '-n',
                    'Namespace'
                )
            );

        $gen->execute();
    }

    public function testSuccessfullyExecuteGeneration()
    {
        $gen = $this->getMock(
            '\Dewdrop\Cli\Command\GenAdminComponent',
            array('writeFile', 'createFolder'),
            array($this->runner, $this->renderer)
        );

        $gen
            ->expects($this->exactly(2))
            ->method('createFolder');

        $gen
            ->expects($this->at(1))
            ->method('writeFile');

        $gen
            ->expects($this->at(2))
            ->method('writeFile');

        $gen
            ->expects($this->at(3))
            ->method('writeFile');

        $gen
            ->expects($this->at(4))
            ->method('writeFile');

        $gen
            ->expects($this->at(5))
            ->method('writeFile');

        $gen
            ->expects($this->at(6))
            ->method('writeFile');

        $gen
            ->expects($this->exactly(5))
            ->method('writeFile');

        $gen
            ->parseArgs(
                array(
                    '-t',
                    'Title',
                    '-f',
                    'Folder'
                )
            );

        $gen->execute();
    }
}
