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

    public function testFetchAllAssocReturnsArrayOfRowsWithNoNumericKeys()
    {
        $sql = 'SELECT * FROM dewdrop_test_fruits';
        $rs  = $this->db->fetchAll($sql, array(), ARRAY_A);
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

    public function testInsertAddsRow()
    {
        $sql   = 'SELECT * FROM dewdrop_test_fruits';
        $start = count($this->db->fetchAll($sql));

        $this->db->insert(
            'dewdrop_test_fruits',
            array(
                'name'                   => 'asdfasdfasdfasdfasdf',
                'is_delicious'           => 1,
                'level_of_deliciousness' => 10
            )
        );

        $this->assertEquals(6, $this->db->lastInsertId());

        $sql = 'SELECT * FROM dewdrop_test_fruits';
        $end = count($this->db->fetchAll($sql));

        $this->assertEquals($start + 1, $end);
    }

    public function testUpdateDoesNotAddRowAndSuccessfullyAltersExistingRow()
    {
        $sql   = 'SELECT * FROM dewdrop_test_fruits';
        $start = count($this->db->fetchAll($sql));

        $columns =  array(
            'name'                   => 'asdfasdfasdfasdfasdf',
            'is_delicious'           => 0,
            'level_of_deliciousness' => 10
        );

        $this->db->update(
            'dewdrop_test_fruits',
            $columns,
            'dewdrop_test_fruit_id = 1'
        );

        $sql = 'SELECT * FROM dewdrop_test_fruits WHERE dewdrop_test_fruit_id = 1';
        $row = $this->db->fetchRow($sql);

        $this->assertEquals(
            $columns,
            array(
                'name'                   => $row['name'],
                'is_delicious'           => $row['is_delicious'],
                'level_of_deliciousness' => $row['level_of_deliciousness']
            )
        );

        $sql = 'SELECT * FROM dewdrop_test_fruits';
        $end = count($this->db->fetchAll($sql));

        $this->assertEquals($start, $end);
    }

    public function testInsertSucceedsWithExprValue()
    {
        $this->db->insert(
            'dewdrop_test_fruits',
            array(
                'name'                   => 'Kiwi',
                'is_delicious'           => 1,
                'level_of_deliciousness' => new Expr(5)
            )
        );

        $this->assertEquals(6, $this->db->lastInsertId());

        $this->assertEquals(
            5,
            $this->db->fetchOne(
                'SELECT level_of_deliciousness
                 FROM dewdrop_test_fruits
                 WHERE dewdrop_test_fruit_id = 6'
            )
        );
    }

    public function testUpdateSucceedsWithExprValue()
    {
        $this->db->update(
            'dewdrop_test_fruits',
            array(
                'level_of_deliciousness' => new Expr(100)
            ),
            'dewdrop_test_fruit_id = 1'
        );

        $this->assertEquals(
            100,
            $this->db->fetchOne(
                'SELECT level_of_deliciousness
                 FROM dewdrop_test_fruits
                 WHERE dewdrop_test_fruit_id = 1'
            )
        );
    }

    public function testFetchPairsReturnsAccurateResults()
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

    public function testFetchRowReturnsAssociatedArrayWithColumnsAsKeys()
    {
        $sql = 'SELECT * FROM dewdrop_test_fruits WHERE dewdrop_test_fruit_id = 1';
        $row = $this->db->fetchRow($sql, array(), ARRAY_A);

        $this->assertTrue(is_array($row));

        $this->assertEquals(
            array('dewdrop_test_fruit_id', 'name', 'is_delicious', 'level_of_deliciousness'),
            array_keys($row)
        );
    }

    public function testFetchOneReturnsScalarValueMatchingFirstColumnInSql()
    {
        $sql = 'SELECT name FROM dewdrop_test_fruits ORDER BY name LIMIT 1';

        $this->assertEquals('Apple', $this->db->fetchOne($sql));
    }
}
