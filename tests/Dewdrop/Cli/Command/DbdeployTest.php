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

    public function testRunningAllChangesetsAddsTwoTablesAndTwoLogEntries()
    {
        $command = $this->getMockCommand();

        $command->parseArgs(array());
        $command->execute();

        $db     = $this->runner->connectDb();
        $tables = $db->listTables();

        $this->assertContains('dewdrop_test_plugins', $tables);
        $this->assertContains('dewdrop_test_dewdrop_tests', $tables);

        $this->assertEquals(
            2,
            $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_dbdeploy_changelog')
        );
    }

    public function testRunningDbdeployTwiceDoesNotReRunScripts()
    {
        // Create the tables with first command
        $command = $this->getMockCommand();
        $command->parseArgs(array());
        $command->execute();

        // Make sure status is called (creation skipped) on second command
        $command = $this->getMockCommand(array('executeStatus'));

        $command
            ->expects($this->once())
            ->method('executeStatus');

        $command->parseArgs(array());
        $command->execute();
    }

    public function testBackfillActionAddsToChangelog()
    {
        $command = $this->getMockCommand();
        $command->parseArgs(array('--action=backfill', '--changeset=plugin', '--revision=1'));
        $command->execute();

        $db = $this->runner->connectDb();

        $this->assertEquals(
            1,
            $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_dbdeploy_changelog')
        );
    }

    public function testBackfillWithoutRevisionCallsAbort()
    {
        $command = $this->getMockCommand();

        $command
            ->expects($this->once())
            ->method('abort');

        $command->parseArgs(array('--action=backfill', '--changeset=plugin'));
        $command->execute();
    }

    public function testBackfillWithoutChangesetCallsAbort()
    {
        $command = $this->getMockCommand();

        $command
            ->expects($this->once())
            ->method('abort');

        $command->parseArgs(array('--action=backfill', '--revision=2'));
        $command->execute();
    }

    public function testHelpContentIncludesNamingConventions()
    {
        $command = $this->getMockCommand();
        $command->help();

        $this->assertTrue($this->renderer->hasOutput('naming conventions'));
    }

    public function testStatusCommandIncludesOneFileForEachChangeset()
    {
        $command = $this->getMockCommand();

        $command->parseArgs(array('--action=status'));
        $command->execute();

        $this->assertTrue($this->renderer->hasOutput('00001-create-dewdrop-test-table.sql'));
        $this->assertTrue($this->renderer->hasOutput('00001-create-plugin-test-table.sql'));
    }

    public function testCallingBackfillWhenAlreadyUpdatedDelegatesToStatus()
    {
        $command = $this->getMockCommand();
        $command->parseArgs(array('--action=backfill', '--changeset=plugin', '--revision=1'));
        $command->execute();

        $db = $this->runner->connectDb();

        $this->assertEquals(
            1,
            $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_dbdeploy_changelog')
        );

        $command = $this->getMockCommand(array('executeStatus'));

        $command
            ->expects($this->once())
            ->method('executeStatus')
            ->will($this->returnValue(true));

        $command->parseArgs(array('--action=backfill', '--changeset=plugin', '--revision=1'));
        $command->execute();
    }

    public function testInvalidFileNameInBackfillAborts()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            'vendor/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-filename'
        );

        $command
            ->expects($this->once())
            ->method('abort')
            ->will($this->returnValue(false));

        $command->parseArgs(array('--action=backfill', '--changeset=plugin', '--revision=1'));
        $this->assertFalse($command->execute());
    }

    public function testInvalidFilenameInUpdateAborts()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            'vendor/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-filename'
        );

        $command
            ->expects($this->once())
            ->method('abort')
            ->will($this->returnValue(false));

        $command->parseArgs(array());
        $this->assertFalse($command->execute());
    }

    public function testInvalidFilenameInStatusAborts()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            'vendor/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-filename'
        );

        $command
            ->expects($this->once())
            ->method('abort')
            ->will($this->returnValue(false));

        $command->parseArgs(array('--action=status'));
        $this->assertFalse($command->execute());
    }

    public function testFailedSqlScriptRunAborts()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            'vendor/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-sql-code'
        );

        $command
            ->expects($this->once())
            ->method('abort')
            ->will($this->returnValue(false));

        $command->parseArgs(array());
        $this->assertFalse($command->execute());

    }

    private function getMockCommand(array $methodsToMock = array())
    {
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\Dbdeploy',
            (count($methodsToMock) ? $methodsToMock : array('abort')),
            array($this->runner, $this->renderer)
        );

        $command->overrideChangesetPath(
            'plugin',
            'vendor/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin/'
        );

        $command->overrideChangesetPath(
            'dewdrop-test',
            'vendor/tests/Dewdrop/Cli/Command/dbdeploy-test/dewdrop-test/'
        );

        $command->overrideChangelogTableName('dewdrop_test_dbdeploy_changelog');

        return $command;
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testOverridingNonExistentChangesetThrowsException()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath('fadfafafafafaf', '');
    }

    public function testCustomMysqlBinaryAffectsRunSqlScript()
    {
        $command = $this->getMockCommand(array('exec'));

        $command
            ->expects($this->once())
            ->method('exec')
            ->with(new \PHPUnit_Framework_Constraint_StringStartsWith('fafafafa'));

        $command->parseArgs(array('--action=status', '--mysql=fafafafa'));
        $command->execute();
    }

    public function testStatusRunOneUpdatedDbShowsExpectedMessage()
    {
        // Run all scripts
        $command = $this->getMockCommand();
        $command->parseArgs(array());
        $command->execute();

        // Check that status says "up to date"
        $command = $this->getMockCommand();
        $command->parseArgs(array());
        $command->execute();

        $this->assertTrue($this->renderer->hasOutput('up to date'));
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
        // @see http://core.trac.wordpress.org/ticket/23085
        $db->getConnection()->db_connect();

        $db->query('DROP TABLE IF EXISTS dewdrop_test_dbdeploy_changelog');
        $db->query('DROP TABLE IF EXISTS dewdrop_test_plugins');
        $db->query('DROP TABLE IF EXISTS dewdrop_test_dewdrop_tests');
    }
}
