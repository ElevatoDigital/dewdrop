<?php

namespace Dewdrop\Cli\Command;

class DbdeployTest extends \PHPUnit_Framework_TestCase
{
    private $renderer;

    private $runner;

    public function setUp()
    {
        $this->renderer = new \Dewdrop\Cli\Renderer\Mock();

        $this->runner = $this->getMock(
            '\Dewdrop\Cli\Run',
            array('halt'),
            array(array(), 'dewdrop-test', $this->renderer)
        );

        $this->cleanDb();
    }

    public function tearDown()
    {
        $this->cleanDb();
    }

    public function testInvalidActionTriggersAbort()
    {
        $command = $this->getMockCommand(array('abort'));

        $command
            ->expects($this->once())
            ->method('abort');

        $command->parseArgs(array('--action=fafafafa'));
        $command->execute();
    }

    public function testChangelogTableIsCreatedSuccessfully()
    {
        $command = $this->getMockCommand(array('executeUpdate'));

        $command
            ->expects($this->once())
            ->method('executeUpdate');

        $command->parseArgs(array());
        $command->execute();

        $db = $this->runner->connectDb();

        $this->assertContains('dewdrop_test_dbdeploy_changelog', $db->listTables());
    }

    public function testFailureToCreateChangelogAborts()
    {
        $command = $this->getMockCommand(array('createChangelog', 'abort', 'executeUpdate'));

        $command
            ->expects($this->once())
            ->method('createChangelog')
            ->will($this->returnValue(false));

        $command
            ->expects($this->once())
            ->method('abort');

        $command->parseArgs(array());
        $command->execute();
    }

    private function getMockCommand(array $methodsToMock)
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\Dbdeploy',
            $methodsToMock,
            array($this->runner, $this->renderer)
        );

        $command->overrideChangelogTableName('dewdrop_test_dbdeploy_changelog');

        return $command;
    }

    /**
     * Because DBUnit does not really provide tools for working with schema, we
     * manually clean up our DB before and after each test.  We run before in
     * case the previous test run failed and we have stuff laying around still.
     * We run after because who likes seeing artifacts of old test runs hanging
     * around their DB?
     */
    private function cleanDb()
    {
        $db = $this->runner->connectDb();

        // Forcing reconnect because of quirky wpdb during testing with logs of queries
        $db->getConnection()->db_connect();

        $db->query('DROP TABLE IF EXISTS dewdrop_test_dbdeploy_changelog');
    }
}
