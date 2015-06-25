<?php

namespace Dewdrop\Db\Dbdeploy;

class ChangelogGatewayTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    private $gateway;

    public function setUp()
    {
        $runner  = new \Dewdrop\Cli\Run();
        $db      = $runner->connectDb();
        $config  = $runner->getPimple()['config']['db'];
        $cliExec = new CliExec($config['type'], $config['username'], $config['password'], $config['host'], $config['name']);

        if (in_array('dewdrop_test_dbdeploy_changelog', $db->listTables())) {
            $db->query('DROP TABLE dewdrop_test_dbdeploy_changelog');
        }

        $this->db = $db;

        $this->gateway = new ChangelogGateway($db, $cliExec, $config['type'], 'dewdrop_test_dbdeploy_changelog');
    }

    public function testCheckingCurrentRevisionOnNonExistentChangesetReturnsZero()
    {
        $this->assertEquals(0, $this->gateway->getCurrentRevisionForChangeset('fafafafa'));
    }

    public function testLoggingAppliedFileChangesCurrentRevisionForChangeset()
    {
        $this->assertEquals(0, $this->gateway->getCurrentRevisionForChangeset('fafafafa'));

        $this->gateway->logAppliedFile(
            'fafafafa',
            1,
            '00001-fafafafa.sql',
            'phpunit',
            date('Y-m-d G:i:s'),
            date('Y-m-d G:i:s')
        );

        $this->assertEquals(1, $this->gateway->getCurrentRevisionForChangeset('fafafafa'));
    }

    public function testChangelogIsCreatedWhenCheckingCurrentRevision()
    {
        $this->assertNotContains('dewdrop_test_dbdeploy_changelog', $this->db->listTables());

        $this->gateway->getCurrentRevisionForChangeset('fafafafa');

        $this->assertContains('dewdrop_test_dbdeploy_changelog', $this->db->listTables());
    }

    public function testChangelogIsCreatedWhenCheckingLoggingAppliedFile()
    {
        $this->assertNotContains('dewdrop_test_dbdeploy_changelog', $this->db->listTables());

        $this->gateway->logAppliedFile(
            'fafafafa',
            1,
            '00001-fafafafa.sql',
            'phpunit',
            date('Y-m-d G:i:s'),
            date('Y-m-d G:i:s')
        );

        $this->assertContains('dewdrop_test_dbdeploy_changelog', $this->db->listTables());
    }
}
