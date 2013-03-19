<?php

namespace Dewdrop\Db;

use Dewdrop\Db\Test\DbTestCase;

class AdapterTest extends DbTestCase
{
    private $db;

    public function setUp()
    {
        parent::setUp();

        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $this->db = new Adapter($wpdb);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->db = null;
    }

    public function getDataSet()
    {
        return $this->createXmlDataSet(__DIR__ . '/datasets/basic-adapter.xml');
    }

    public function testFetchAllAssoc()
    {
        $sql = 'SELECT * FROM dewdrop_test_fruits';
        $rs  = $this->db->fetchAll($sql);
        $row = current($rs);

        $this->assertTrue(is_array($row));
        $this->assertEquals('dewdrop_test_fruit_id', current(array_keys($row)));

        $int = false;

        foreach (array_keys($row) as $index) {
            if (!is_string($index)) {
                $int = true;
            }
        }

        $this->assertFalse($int);
    }

    public function testFetchPairs()
    {
        $sql   = 'SELECT dewdrop_test_fruit_id, name FROM dewdrop_test_fruits';
        $pairs = $this->db->fetchPairs($sql);

        $this->assertTrue(is_array($pairs));
        $this->assertEquals(5, count($pairs));

        $int    = true;
        $string = true;

        foreach ($pairs as $fruitId => $name) {
            if ($fruitId !== (int) $fruitId) {
                $int = false;
            }

            if (!is_string($name)) {
                $string = false;
            }
        }

        $this->assertTrue($int && $string);
    }

    public function testFetchRow()
    {
        $sql = 'SELECT * FROM dewdrop_test_fruits WHERE dewdrop_test_fruit_id = 1';
        $row = $this->db->fetchRow($sql);

        $this->assertTrue(is_array($row));
    }

    public function testFetchOne()
    {
        $sql = 'SELECT name FROM dewdrop_test_fruits ORDER BY name LIMIT 1';

        $this->assertEquals('Apple', $this->db->fetchOne($sql));
    }
}
