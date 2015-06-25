<?php

namespace Dewdrop\Db\Dbdeploy\Command;

use Dewdrop\Db\Adapter\Mock as MockAdapter;
use Dewdrop\Db\Dbdeploy\Changeset;
use Dewdrop\Db\Dbdeploy\CliExec;
use Dewdrop\Db\Dbdeploy\ChangelogGateway;

class StatusTest extends \PHPUnit_Framework_TestCase
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

    public function testTwoAvailableChangesWhenUsingDummyChangeset()
    {
        $changeset = new Changeset(
            $this->getMockGateway(0),
            'dummy-changeset',
            __DIR__ . '/../dummy-changeset'
        );

        $command = new Status(array($changeset));

        $command->execute();

        $this->assertEquals(2, $command->getAvailableChangesCount());
    }
}
