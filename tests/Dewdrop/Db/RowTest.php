<?php

namespace Dewdrop\Db;

use Dewdrop\Test\DbTestCase;

class RowTest extends DbTestCase
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

    public function testCanSetSingleValueWithValidColumn()
    {
        $row = $this->table->createRow();

        $row->set('name', 'Test');

        $this->assertEquals('Test', $row->get('name'));
    }

    public function testCanSetMultipleValuesWithValidColumns()
    {
        $row = $this->table->createRow();

        $row->set(
            array(
                'name'         => 'Test',
                'is_delicious' => 1
            )
        );

        $this->assertEquals('Test', $row->get('name'));
        $this->assertEquals(1, $row->get('is_delicious'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCannotSetInvalidColumn()
    {
        $row = $this->table->createRow();

        $row->set('fafafafafa', false);
    }

    public function testWhenSettingColumnToBooleanValueItIsCastToInt()
    {
        $row = $this->table->createRow();

        $row->set('is_delicious', true);
        $this->assertEquals(1, $row->get('is_delicious'));

        $row->set('is_delicious', false);
        $this->assertEquals(0, $row->get('is_delicious'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testGettingUnknownColumnThrowsException()
    {
        $row = $this->table->createRow();

        $row->get('fafafafa');
    }

    public function testGetTableReturnsExpectedObject()
    {
        $row   = $this->table->createRow();
        $table = $row->getTable();

        $this->assertInstanceOf('\Dewdrop\Db\Table', $table);

        $this->assertEquals(
            spl_object_hash($table),
            spl_object_hash($this->table)
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testInvalidColumnsPassedToConstructorAreSilentlyUnset()
    {
        $row = $this->table->createRow(
            array(
                'fafafafa' => 'Test'
            )
        );

        $row->get('fafafafa');
    }

    public function testFieldObjectsRetrievedFromRowAreAssociatedWithIt()
    {
        $row   = $this->table->createRow();
        $field = $row->field('name');

        $field->setValue('Test');

        $this->assertEquals('Test', $row->get('name'));

        $row->set('name', '2nd Test');

        $this->assertEquals('2nd Test', $field->getValue());
    }

    public function testFieldObjectsOriginallyFromTableGainAssociationWithRowOnSecondCall()
    {
        $this->table->field('name');

        $row   = $this->table->createRow();
        $field = $row->field('name');

        $field->setValue('Test');

        $this->assertEquals('Test', $row->get('name'));

        $row->set('name', '2nd Test');

        $this->assertEquals('2nd Test', $field->getValue());
    }

    public function testSavingNewRowPerformsAnInsert()
    {
        $row = $this->table->createRow();

        $row->set(
            array(
                'name'                   => 'Grape',
                'is_delicious'           => 1,
                'level_of_deliciousness' => 9
            )
        );

        $row->save();

        $this->assertEquals(6, $row->get('dewdrop_test_fruit_id'));
        $this->assertEquals(6, $this->db->lastInsertId());
        $this->assertEquals('Grape', $row->get('name'));
    }

    public function testSavingAnExistingRowPerformsAnUpdate()
    {
        $row = $this->table->find(1);

        $row->name = 'Grape';

        $row->save();

        $this->assertEquals(5, $this->db->fetchOne('SELECT COUNT(*) FROM dewdrop_test_fruits'));
        $this->assertEquals(1, $row->get('dewdrop_test_fruit_id'));
    }

    public function testDelete()
    {
        $table       = 'dewdrop_test_fruits';
        $idField     = 'dewdrop_test_fruit_id';
        $fetchAllSql = "SELECT * FROM {$table} ORDER BY {$idField}";

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(5, count($fruits));
        $this->assertEquals(1, $fruits[0][$idField]);

        $fruit = $this->table->find(1);

        $this->assertSame(1, $fruit->delete());

        $this->assertNull($fruit->get('dewdrop_test_fruit_id'));
        $this->assertNull($fruit->get('name'));
        $this->assertNull($fruit->get('is_delicious'));
        $this->assertNull($fruit->get('level_of_deliciousness'));

        $fruits = $this->db->fetchAll($fetchAllSql);
        $this->assertSame(4, count($fruits));
        $this->assertEquals(2, $fruits[0][$idField]);
    }

    public function testSettingPropertyWithObjectSyntaxIsAllowed()
    {
        $row = $this->table->createRow();
        $row->name = 'Apple';
        $this->assertEquals($row->get('name'), 'Apple');
    }

    public function testSettingPropertyWithArraySyntaxIsAllowed()
    {
        $row = $this->table->createRow();
        $row['name'] = 'Apple';
        $this->assertEquals($row->get('name'), 'Apple');
    }

    public function testGettingPropertyWithObjectSyntaxIsAllowed()
    {
        $row = $this->table->createRow();
        $row->set('name', 'Apple');
        $this->assertEquals($row->name, 'Apple');
    }

    public function testGettingPropertyWithArraySyntaxIsAllowed()
    {
        $row = $this->table->createRow();
        $row->set('name', 'Apple');
        $this->assertEquals($row['name'], 'Apple');
    }

    public function testCanCheckForColumnExistenceWithHasMethod()
    {
        $row = $this->table->createRow();
        $this->assertTrue($row->has('name'));
        $this->assertFalse($row->has('fafafafa'));
    }

    public function testCanCheckForColumnExistenceWithObjectSyntax()
    {
        $row = $this->table->createRow();
        $this->assertTrue(isset($row->name));
        $this->assertFalse(isset($row->fafafafa));
    }

    public function testCanCheckForColumnExistenceWithArraySyntax()
    {
        $row = $this->table->createRow();
        $this->assertTrue(isset($row['name']));
        $this->assertFalse(isset($row['fafafafa']));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCannotUnsetColumnWithObjectSyntax()
    {
        $row = $this->table->createRow();
        unset($row->name);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCannotUnsetColumnWithArraySyntax()
    {
        $row = $this->table->createRow();
        unset($row['name']);
    }
}
