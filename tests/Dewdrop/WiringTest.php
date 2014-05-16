<?php

namespace Dewdrop;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\Wiring;

class WiringTest extends BaseTestCase
{
    public function setUp()
    {
        if (!defined('WPINC')) {
            $this->markTestSkipped('Not running in a WP environment.');
        }
    }

    public function testCanOverrideDefaultPaths()
    {
        $paths = new \Dewdrop\Paths();

        $wiring = new Wiring(
            array(
                'paths' => $paths
            )
        );

        $this->assertEquals(
            spl_object_hash($paths),
            spl_object_hash($wiring->getPaths())
        );
    }

    public function testCanOverrideDefaultDbAdapter()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        $wiring = new Wiring(
            array(
                'db' => $db
            )
        );

        $this->assertEquals(
            spl_object_hash($db),
            spl_object_hash($wiring->getDb())
        );
    }

    public function testCanRegisterAdminComponents()
    {
        $paths = $this->getMock(
            '\Dewdrop\Paths',
            array('getAdmin'),
            array()
        );

        $paths
            ->expects($this->any())
            ->method('getAdmin')
            ->will($this->returnValue(__DIR__ . '/wiring-admin-components'));

        $wiring = $this->getMock(
            '\Dewdrop\Wiring',
            array('isAdmin'),
            array(
                array(
                    'autoRegister' => false,
                    'paths'        => $paths
                )
            )
        );

        $wiring
            ->expects($this->once())
            ->method('isAdmin')
            ->will($this->returnValue(true));

        $wiring->autoRegisterAdminComponents();
    }

    public function testCanRetrieveReferenceToInflector()
    {
        $wiring = new Wiring();

        $this->assertInstanceOf('\Dewdrop\Inflector', $wiring->getInflector());
    }

    public function testCanRetrieveReferenceToPaths()
    {
        $wiring = new Wiring();

        $this->assertInstanceOf('\Dewdrop\Paths', $wiring->getPaths());
    }
}
