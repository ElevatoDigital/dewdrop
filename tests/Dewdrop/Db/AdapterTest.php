<?php

namespace Dewdrop\Db;

class AdapterTest extends \PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $this->db = new Adapter($wpdb);
    }

    public function tearDown()
    {
        $this->db = null;
    }

    public function testFetchAllAssoc()
    {
        $sql = 'SELECT * FROM fruits';
        $rs  = $this->db->fetchAll($sql);
        $row = current($rs);

        $this->assertTrue(is_array($row));
        $this->assertEquals('fruit_id', current(array_keys($row)));

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
        $this->markTestSkipped(
            'Need to create test dataset'
        );

        $sql   = 'SELECT fruit_id, name FROM fruits';
        $pairs = $this->db->fetchPairs($sql);

        $this->assertTrue(is_array($pairs));
        $this->assertEquals(6, count($pairs));

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
        $sql = 'SELECT * FROM fruits WHERE fruit_id = 1';
        $row = $this->db->fetchRow($sql);

        $this->assertTrue(is_array($row));
    }

    public function testFetchOne()
    {
        $this->markTestSkipped(
            'Need to create test dataset'
        );

        $sql = 'SELECT name FROM fruits ORDER BY name LIMIT 1';

        $this->assertEquals('Apple', $this->db->fetchOne($sql));
    }
}
