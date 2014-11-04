<?php

namespace Dewdrop\Db;

use Dewdrop\Test\DbTestCase;

class TableTest extends DbTestCase
{
    /**
     * @var \DewdropTest\DewdropTestFruits
     */
    private $table;

    /**
     * @var Adapter
     */
    private $db;

    public function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/table/DewdropTestFruits.php';

        $this->db = $GLOBALS['dewdrop_pimple']['db'];

        $this->table = new \DewdropTest\DewdropTestFruits($this->db);
    }

    public function getDataSet()
    {
        return $this->createXmlDataSet(__DIR__ . '/datasets/basic-adapter.xml');
    }

    public function testCustomizeFieldCallbackIsRunUponRetrieval()
    {
        $this->table->customizeField(
            'name',
            function ($field) {
                $field->setLabel('fafafafa');
            }
        );

        $this->assertEquals(
            'fafafafa',
            $this->table->field('name')->getLabel()
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCustomizingUnkownFieldThrowsException()
    {
        $this->table->customizeField(
            'fafafafa',
            function ($field) {

            }
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testRetrievingUnknownFieldThrowsAnException()
    {
        $this->table->field('fafafafa');
    }

    public function testRetrievingFieldMultipleTimesReturnsSameObject()
    {
        $field = $this->table->field('name');

        $this->assertEquals($field, $this->table->field('name'));

        $this->assertEquals(
            spl_object_hash($field),
            spl_object_hash($this->table->field('name'))
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testFailingToSetTableNameInInitMethodThrowsException()
    {
        require_once __DIR__ . '/table/NoTableName.php';
        $table = new \DewdropTest\NoTableName($this->db);
    }

    public function testSelectReturnsDewdropDbSelectObject()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Select', $this->table->select());
    }

    public function testSelectAlwaysReturnsNewObject()
    {
        $select = $this->table->select();

        $this->assertNotEquals(spl_object_hash($select), spl_object_hash($this->table->select()));
    }

    public function testGetTableNameMatchesNameAsSetInInit()
    {
        $this->assertEquals('dewdrop_test_fruits', $this->table->getTableName());
    }

    public function testSingularTitleIsPulledFromMetadataIfNotSet()
    {
        $this->assertEquals('Dewdrop Test Fruit', $this->table->getSingularTitle());
        $this->assertEquals('Dewdrop Test Fruit', $this->table->getMetadata('titles', 'singular'));
    }

    public function testPluralTitleIsPulledFromMetadataIfNotSet()
    {
        $this->assertEquals('Dewdrop Test Fruits', $this->table->getPluralTitle());
        $this->assertEquals('Dewdrop Test Fruits', $this->table->getMetadata('titles', 'plural'));
    }

    public function testCanManuallyOverrideSingularTitleValue()
    {
        $this->table->setSingularTitle('fafafafa');

        $this->assertEquals('fafafafa', $this->table->getSingularTitle());
    }

    public function testCanManuallyOverridePluralTitleValue()
    {
        $this->table->setPluralTitle('fafafafas');

        $this->assertEquals('fafafafas', $this->table->getPluralTitle());
    }

    public function testRetrieveAnUnknownMetadataSectionReturnsFalse()
    {
        $this->assertFalse($this->table->getMetadata('fafafafa'));
    }

    public function testRetrieveEntiretyOfMetadataArray()
    {
        $keys = array_keys($this->table->getMetadata());

        $this->assertTrue(in_array('titles', $keys));
        $this->assertTrue(in_array('columns', $keys));
    }

    public function testGetPrimaryKeyReturnsArrayWithCorrectColumn()
    {
        $pkey = $this->table->getPrimaryKey();

        $this->assertTrue(is_array($pkey));
        $this->assertEquals(1, count($pkey));
        $this->assertEquals('dewdrop_test_fruit_id', current($pkey));
    }

    public function testGetAdapterReturnsExpectedObject()
    {
        $this->assertInstanceOf('\Dewdrop\Db\Adapter', $this->table->getAdapter());
    }

    public function testInsertReturnsLastInsertId()
    {
        $id = $this->table->insert(
            array(
                'name'                   => 'Peach',
                'is_delicious'           => 1,
                'level_of_deliciousness' => 6
            )
        );

        $this->assertEquals(6, $id);
    }

    public function testUpdateSuccessfullySavesExistingRowAndReturnsRowsAffected()
    {
        $affected = $this->table->update(
            array(
                'name'                   => 'Star Fruit',
                'is_delicious'           => 1,
                'level_of_deliciousness' => 4
            ),
            'dewdrop_test_fruit_id = 1'
        );

        $this->assertEquals(1, $affected);
        $this->assertEquals(5, $this->db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_fruits'));

        $this->assertEquals(
            'Star Fruit',
            $this->db->fetchOne('SELECT name FROM dewdrop_test_fruits WHERE dewdrop_test_fruit_id = 1')
        );
    }

    public function testFindReturnsExpectedRow()
    {
        $row = $this->table->find(1);

        $this->assertEquals('Apple', $row->get('name'));
    }

    public function testCreateRowWithNoDataReturnsBlankRowObject()
    {
        $row = $this->table->createRow();

        $this->assertNull($row->get('dewdrop_test_fruit_id'));
        $this->assertNull($row->get('name'));

        // This field has a default value in the schema, so its an exception
        $this->assertEquals(1, $row->get('is_delicious'));

        // This field has a default value in the schema, so its an exception
        $this->assertEquals(0, $row->get('level_of_deliciousness'));
    }

    public function testCreateRowWithInitialDataSetsFieldsCorrectly()
    {
        $row = $this->table->createRow(
            array(
                'name'         => 'Blood Orange',
                'is_delicious' => 1
            )
        );

        $this->assertEquals('Blood Orange', $row->get('name'));
        $this->assertEquals(1, $row->get('is_delicious'));
    }

    public function testDelete()
    {
        $table       = 'dewdrop_test_fruits';
        $idField     = 'dewdrop_test_fruit_id';
        $fetchAllSql = "SELECT * FROM {$table} ORDER BY {$idField}";

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(5, count($fruits));
        $this->assertEquals(1, $fruits[0][$idField]);

        $this->assertSame(1, $this->table->delete("{$idField} = 1"));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(4, count($fruits));
        $this->assertEquals(2, $fruits[0][$idField]);

        $this->assertSame(0, $this->table->delete("{$idField} = 7"));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(4, count($fruits));
        $this->assertEquals(2, $fruits[0][$idField]);

        $this->assertSame(1, $this->table->delete(array("{$idField} = 2", 'is_delicious')));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(3, count($fruits));
        $this->assertEquals(3, $fruits[0][$idField]);
    }

    public function testFetchAdminListing()
    {
        $fruits = $this->table->fetchAdminListing();

        $this->assertInternalType('array', $fruits);
        $this->assertSame(5, count($fruits));
        $previousFruitId = 0;
        foreach ($fruits as $fruit) {
            $this->assertInternalType('array', $fruit);
            $this->assertArrayHasKey('dewdrop_test_fruit_id', $fruit);
            $this->assertGreaterThan($previousFruitId, $fruit['dewdrop_test_fruit_id']);
            $previousFruitId = $fruit['dewdrop_test_fruit_id'];
            $this->assertArrayHasKey('name', $fruit);
            $this->assertArrayHasKey('is_delicious', $fruit);
            $this->assertArrayHasKey('level_of_deliciousness', $fruit);
        }
    }
}
