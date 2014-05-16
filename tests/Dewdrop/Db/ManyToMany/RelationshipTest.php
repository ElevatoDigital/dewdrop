<?php

namespace Dewdrop\Db\ManyToMany;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\ManyToMany\Relationship;
use Dewdrop\Test\DbTestCase;

class RelationshipTest extends DbTestCase
{
    private $db;

    private $table;

    public function setUp()
    {
        parent::setUp();

        $this->db = $GLOBALS['dewdrop_pimple']['db'];

        require_once __DIR__ . '/table-classes/Animals.php';
        $this->table = new \DewdropTest\Db\ManyToMany\Animals($this->db);
    }

    public function getDataSet()
    {
        return $this->createXmlDataSet(__DIR__ . '/datasets/basic-many-to-many.xml');
    }

    public function withMockMetadata($file)
    {
        $relationship = $this->getMock(
            '\Dewdrop\Db\ManyToMany\Relationship',
            array('loadXrefTableMetadata'),
            array($this->table, 'dewdrop_test_fruits_eaten_by_animals')
        );

        $relationship
            ->expects($this->any())
            ->method('loadXrefTableMetadata')
            ->will($this->returnValue(require __DIR__ . '/table-metadata/' . $file));

        return $relationship;
    }

    public function testCanSaveNewValuesViaRow()
    {
        $row = $this->table->find(1);

        $row
            ->set('fruits', array(1, 2))
            ->save();

        $row   = $this->table->find(1);
        $value = $row->get('fruits');

        sort($value);

        $this->assertEquals(array(1, 2), $value);
    }

    public function testWillLoadInitialValuesForRow()
    {
        $row   = $this->table->find(1);
        $value = $row->get('fruits');

        $this->assertEquals(1, count($value));
        $this->assertEquals(1, current($value));

        $row = $this->table->createRow();

        $row
            ->set('name', 'T-Rex')
            ->set('is_fierce', true)
            ->set('is_cute', false)
            ->save();

        $this->assertEquals(0, count($row->get('fruits')));
    }

    public function testInitialValueWithBeEmptyArrayForNewRow()
    {
        $row = $this->table->createRow();

        $this->assertEquals(array(), $row->get('fruits'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testSettingUnkonwnOptionWillThrowException()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setOptions(
            array(
                'fafafafa' => 'fa'
            )
        );
    }

    public function testCanSetMultipleValidOptionsAtOnce()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setOptions(
            array(
                'sourceColumnName'     => 'fafafafa',
                'xrefAnchorColumnName' => 'fofofofo',
            )
        );

        $this->assertEquals('fafafafa', $relationship->getSourceColumnName());
        $this->assertEquals('fofofofo', $relationship->getXrefAnchorColumnName());
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfSourceTableReferenceWillThrowException()
    {
        $relationship = $this->withMockMetadata('no_source_table_reference.php');
        $relationship->getSourceColumnName();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfXrefAnchorColumnWillThrowException()
    {
        $relationship = $this->withMockMetadata('no_source_table_reference.php');
        $relationship->getXrefAnchorColumnName();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfReferenceTableWillThrowException()
    {
        $relationship = $this->withMockMetadata('no_option_table_reference.php');
        $relationship->getReferenceTableName();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testLackOfReferenceColumnWillThrowException()
    {
        $relationship = $this->withMockMetadata('no_option_table_reference.php');
        $relationship->getReferenceColumnName();
    }

    public function testCanOverrideSourceColumnName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setSourceColumnName('fafafafa');

        $this->assertEquals('fafafafa', $relationship->getSourceColumnName());
    }

    public function testCanOverrideXrefAnchorColumnName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setXrefAnchorColumnName('fafafafa');

        $this->assertEquals('fafafafa', $relationship->getXrefAnchorColumnName());
    }

    public function testCanOverrideXrefReferenceColumnName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setXrefReferenceColumnName('fafafafa');

        $this->assertEquals('fafafafa', $relationship->getXrefReferenceColumnName());
    }

    public function testCanOverrideReferenceTableName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setReferenceTableName('fafafafa');

        $this->assertEquals('fafafafa', $relationship->getReferenceTableName());
    }

    public function testCanOverrideReferenceColumnName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setReferenceColumnName('fafafafa');

        $this->assertEquals('fafafafa', $relationship->getReferenceColumnName());
    }

    public function testCanAutoDetectReferenceTableName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $this->assertEquals('dewdrop_test_fruits', $relationship->getReferenceTableName());
    }

    public function testCanAutoDetectReferenceColumnName()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $this->assertEquals('dewdrop_test_fruit_id', $relationship->getReferenceColumnName());
    }

    public function testReturnsXrefTableReferenceColumnMetatadata()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');
        $metadata     = $relationship->getFieldMetadata();

        $this->assertEquals('fruit_id', $metadata['COLUMN_NAME']);
    }

    public function testReturnsReferenceInfoForOptionPairsUtility()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $this->assertEquals(
            array(
                'table'  => 'dewdrop_test_fruits',
                'column' => 'dewdrop_test_fruit_id'
            ),
            $relationship->getOptionPairsReference()
        );
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testUnavailableOptionPairsReferenceThrowsException()
    {
        $relationship = new Relationship($this->table, 'dewdrop_test_fruits_eaten_by_animals');

        $relationship->setXrefReferenceColumnName('fafafafa');

        $relationship->getOptionPairsReference();
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testMissingXrefTableMetadataThrowsException()
    {
        $relationship = new Relationship($this->table, 'fafafafa');

        $relationship->getSourceColumnName();
    }
}
