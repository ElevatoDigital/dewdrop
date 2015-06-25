<?php

namespace Dewdrop\Db\Dbdeploy;

use Dewdrop\Db\Adapter\Mock as MockAdapter;

class ChangesetTest extends \PHPUnit_Framework_TestCase
{
    public function getMockGateway($revision)
    {
        $cliExec = new CliExec('pgsql', 'mock', 'mock', 'mock', 'mock');
        $db      = new MockAdapter();

        $gateway = $this->getMock(
            '\Dewdrop\Db\Dbdeploy\ChangelogGateway',
            array('tableExists', 'getCurrentRevisionForChangeset'),
            array($db, $cliExec, 'pgsql')
        );

        $gateway->expects($this->any())
            ->method('tableExists')
            ->will($this->returnValue(true));

        $gateway->expects($this->any())
            ->method('getCurrentRevisionForChangeset')
            ->will($this->returnValue($revision));

        return $gateway;
    }

    public function testCanRetrieveChangesetName()
    {
        $changeset = new Changeset($this->getMockGateway(8), 'fafafafa', __DIR__ . '/dummy-changeset');

        $this->assertEquals('fafafafa', $changeset->getName());
    }

    public function testGetCurrentRevisionAsksTheGateway()
    {
        $changeset = new Changeset($this->getMockGateway(8), 'dummy', __DIR__ . '/dummy-changeset');

        $this->assertEquals(8, $changeset->getCurrentRevision());
    }

    public function testOneAvailableRevisionWhenOneAlreadyAppliedInDummySet()
    {
        $changeset = new Changeset($this->getMockGateway(1), 'dummy', __DIR__ . '/dummy-changeset');

        $this->assertEquals(1, count($changeset->getNewFiles()));
    }

    public function testTwoAvailableFilesOverallInDummySet()
    {
        $changeset = new Changeset($this->getMockGateway(0), 'dummy', __DIR__ . '/dummy-changeset');

        $this->assertEquals(2, count($changeset->getNewFiles()));
        $this->assertEquals(2, $changeset->getAvailableRevision());
    }

    public function testFirstChangeIsInAppliedFilesInDummySet()
    {
        $changeset = new Changeset($this->getMockGateway(1), 'dummy', __DIR__ . '/dummy-changeset');

        $this->assertArrayHasKey(1, $changeset->getAppliedFiles());

        $this->assertTrue(
            in_array(
                __DIR__ . '/dummy-changeset/00001-change-one.sql',
                $changeset->getAppliedFiles()
            )
        );

        $this->assertFalse(
            in_array(
                __DIR__ . '/dummy-changeset/00002-change-two.sql',
                $changeset->getAppliedFiles()
            )
        );
    }

    /**
     * @expectedException \Dewdrop\Db\Dbdeploy\Exception\InvalidFilename
     */
    public function testInvalidFileNameThrowsException()
    {
        $changeset = new Changeset($this->getMockGateway(0), 'invalid', __DIR__ . '/invalid-changeset');

        $changeset->getNewFiles();
    }
}
