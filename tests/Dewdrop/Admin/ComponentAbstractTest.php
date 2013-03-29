<?php

namespace Dewdrop\Admin;

use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Request;

class ComponentAbstractTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    private $paths;

    private $request;

    public function setUp()
    {
        $this->paths   = new Paths();
        $this->request = new Request();

        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $this->db = new Adapter($wpdb);

        require_once __DIR__ . '/test-components/animals/Component.php';
        $this->component = new \DewdropTest\Admin\Animals\Component($this->db, $this->paths, $this->request);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testComponentWithEmptyInitThrowsException()
    {
        require_once __DIR__ . '/test-components/insufficient-init-method/Component.php';
        $component = new \DewdropTest\Admin\InsufficientInitMethod\Component($this->db, $this->paths, $this->request);
    }

    public function testGetDbReturnsAdapter()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Adapter', $this->component->getDb());
    }
}
