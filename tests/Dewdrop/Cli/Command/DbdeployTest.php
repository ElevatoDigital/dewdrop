<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Env;
use Dewdrop\Wp\Env as WpEnv;
use Dewdrop\Paths;

class DbdeployTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Dewdrop\Cli\Renderer\Mock
     */
    private $renderer;

    /**
     * @var \Dewdrop\Cli\Run
     */
    private $runner;

    /**
     * @var \Dewdrop\Paths
     */
    private $paths;

    public function setUp()
    {
        $this->renderer = new \Dewdrop\Cli\Renderer\Mock();
        $this->paths    = new Paths();

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
            $db->fetchOne("SELECT COUNT(*) FROM dewdrop_test_dbdeploy_changelog")
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
        $env = Env::getInstance();

        $command = $this->getMockCommand();
        $command->parseArgs(array('--action=backfill', '--changeset=' . $env->getProjectNoun(), '--revision=1'));
        $command->execute();

        $db = $this->runner->connectDb();

        $this->assertEquals(
            1,
            $db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_dbdeploy_changelog')
        );
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception
     */
    public function testBackfillWithoutRevisionThrowsException()
    {
        $command = $this->getMockCommand();
        $command->parseArgs(array('--action=backfill', '--changeset=plugin'));
        $command->execute();
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception
     */
    public function testBackfillWithoutChangesetThrowsException()
    {
        $command = $this->getMockCommand();
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
        $env = Env::getInstance();

        $command = $this->getMockCommand();
        $command->parseArgs(array('--action=backfill', '--changeset=' . $env->getProjectNoun(), '--revision=1'));
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

        $command->parseArgs(array('--action=backfill', '--changeset=' . $env->getProjectNoun(), '--revision=1'));
        $command->execute();
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception\InvalidFilename
     */
    public function testInvalidFileNameInBackfillThrowsException()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-filename'
        );

        $command->parseArgs(array('--action=backfill', '--changeset=plugin', '--revision=1'));
        $this->assertFalse($command->execute());
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception\InvalidFilename
     */
    public function testInvalidFilenameInUpdateThrowsException()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-filename'
        );

        $command->parseArgs(array());
        $this->assertFalse($command->execute());
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception\InvalidFilename
     */
    public function testInvalidFilenameInStatusThrowsException()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-filename'
        );

        $command->parseArgs(array('--action=status'));
        $this->assertFalse($command->execute());
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception\ScriptExecutionFailed
     */
    public function testFailedSqlScriptRunAborts()
    {
        $command = $this->getMockCommand();

        $command->overrideChangesetPath(
            'plugin',
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin-bad-sql-code'
        );

        $command->parseArgs(array());
        $this->assertFalse($command->execute());
    }

    /**
     * @param array $methodsToMock
     * @return \PHPUnit_Framework_MockObject_MockObject|\Dewdrop\Cli\Command\Dbdeploy
     */
    private function getMockCommand(array $methodsToMock = array())
    {
        /* @var $command \PHPUnit_Framework_MockObject_MockObject|\Dewdrop\Cli\Command\Dbdeploy */
        $command = $this->getMock(
            '\Dewdrop\Cli\Command\Dbdeploy',
            (count($methodsToMock) ? $methodsToMock : array('abort')),
            array($this->runner, $this->renderer)
        );

        $env = Env::getInstance();

        $dbTypeSuffix = 'pgsql';

        if ($env instanceof WpEnv) {
            $dbTypeSuffix = 'mysql';
        }

        $command->overrideChangesetPath(
            $env->getProjectNoun(),
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/plugin/' . $dbTypeSuffix
        );

        $command->overrideChangesetPath(
            'dewdrop-test',
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/dewdrop-test/' . $dbTypeSuffix
        );

        $command->overrideChangesetPath(
            'dewdrop-core',
            $this->paths->getDewdropLib() . '/tests/Dewdrop/Cli/Command/dbdeploy-test/dewdrop-core/'
        );

        $command->overrideChangelogTableName('dewdrop_test_dbdeploy_changelog');

        return $command;
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

        if ($db->getConnection() instanceof \wpdb) {
            // Forcing reconnect because of quirky wpdb during testing with lots of queries
            // @see http://core.trac.wordpress.org/ticket/23085
            $db->getConnection()->db_connect();
        }

        $db->query('DROP TABLE IF EXISTS dewdrop_test_dbdeploy_changelog');
        $db->query('DROP TABLE IF EXISTS dewdrop_test_plugins');
        $db->query('DROP TABLE IF EXISTS dewdrop_test_dewdrop_tests');
    }
}
