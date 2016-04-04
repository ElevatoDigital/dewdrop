<?php

namespace Dewdrop\Db;

use Dewdrop\Db\Adapter;
use Dewdrop\Test\DbTestCase;

class AdapterTest extends DbTestCase
{
    /**
     * @var Adapter
     */
    private $db;

    public function setUp()
    {
        parent::setUp();

        $this->db = $GLOBALS['dewdrop_pimple']['db'];
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
        $rs  = $this->db->fetchAll($sql, array(), Adapter::ARRAY_A);
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

    public function testFetchAllWithGeneratorAssocReturnsGeneratorOfRowsWithNoNumericKeys()
    {
        $sql = 'SELECT * FROM dewdrop_test_fruits';

        foreach ($this->db->fetchAllWithGenerator($sql, [], Adapter::ARRAY_A) as $row) {
            $this->assertInternalType('array', $row);
            $rowKeys = array_keys($row);
            $this->assertSame('dewdrop_test_fruit_id', current($rowKeys));
            foreach ($rowKeys as $key) {
                $this->assertInternalType('string', $key);
            }
        }
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
        $row = $this->db->fetchRow($sql, array(), Adapter::ARRAY_A);

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

    public function testFetchRowWithNoResultsReturnsNull()
    {
        $this->assertNull(
            $this->db->fetchRow('SELECT * FROM dewdrop_test_fruits WHERE dewdrop_test_fruit_id = 10')
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testInvalidLimitCountThrowsException()
    {
        $this->db->limit('SELECT * FROM dewdrop_test_fruits', -1);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testInvalidLimitOffsetThrowsException()
    {
        $this->db->limit('SELECT * FROM dewdrop_test_fruits', 1, -10);
    }

    public function testListTablesReturnsSingleColumnArrayWithTableNames()
    {
        $tables = $this->db->listTables();

        $stringValues = true;
        $numKeys      = true;

        foreach ($tables as $key => $table) {
            if (!is_int($key)) {
                $numKeys = false;
            }

            if (!is_string($table)) {
                $stringValues = false;
            }
        }

        $this->assertTrue($numKeys);
        $this->assertTrue($stringValues);
        $this->assertTrue(in_array('dewdrop_test_fruits', $tables));
    }

    public function testDescribeTableReturnsAccurateListingOfFruitsColumns()
    {
        $columns = $this->db->describeTable('dewdrop_test_fruits');

        $this->assertTrue(array_key_exists('dewdrop_test_fruit_id', $columns));

        $this->assertTrue($columns['dewdrop_test_fruit_id']['IDENTITY']);
        $this->assertEquals(1, $columns['dewdrop_test_fruit_id']['PRIMARY_POSITION']);
        $this->assertEquals(4, count($columns));
    }

    public function testGetConnectionReturnsWpdbInstance()
    {
        if (!defined('WPINC')) {
            $this->markTestSkipped('Not running in WP plugin context');
        }

        $this->assertInstanceOf('\wpdb', $this->db->getConnection());
    }

    public function testDelete()
    {
        $table       = 'dewdrop_test_fruits';
        $idField     = 'dewdrop_test_fruit_id';
        $fetchAllSql = "SELECT * FROM {$table} ORDER BY {$idField}";

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(5, count($fruits));
        $this->assertEquals(1, $fruits[0][$idField]);

        $this->assertSame(1, $this->db->delete($table, "{$idField} = 1"));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(4, count($fruits));
        $this->assertEquals(2, $fruits[0][$idField]);

        $this->assertSame(0, $this->db->delete($table, "{$idField} = 7"));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(4, count($fruits));
        $this->assertEquals(2, $fruits[0][$idField]);

        $this->assertSame(1, $this->db->delete($table, array("{$idField} = 2", 'is_delicious')));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(3, count($fruits));
        $this->assertEquals(3, $fruits[0][$idField]);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testBadFetchAllThrowsException()
    {
        $this->db->fetchAll('not even close to valid sql');
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testBadFetchColThrowsException()
    {
        $this->db->fetchCol('not even close to valid sql');
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testBadFetchOneThrowsException()
    {
        $this->db->fetchOne('not even close to valid sql');
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testBadFetchQueryThrowsException()
    {
        $this->db->query('not even close to valid sql');
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Driver\DriverInterface', $this->db->getDriver());
    }
}
