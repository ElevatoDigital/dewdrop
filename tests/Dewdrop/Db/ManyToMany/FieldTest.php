<?php

namespace Dewdrop\Db\ManyToMany;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\ManyToMany\Relationship as ManyToManyRelationship;
use Dewdrop\Test\DbTestCase;

class FieldTest extends DbTestCase
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

    public function testRetrievingFieldFromTableSetsRelationship()
    {
        $field = $this->table->field('fruits');

        $this->assertInstanceOf(
            '\Dewdrop\Db\ManyToMany\Relationship',
            $field->getManyToManyRelationship()
        );
    }

    public function testWhenRequiredInputFilterDoesNotAllowEmpty()
    {
        $field = $this->table->field('fruits');

        $field->setRequired(true);

        $this->assertFalse($field->getInputFilter()->allowEmpty());
    }

    public function testWhenNotRequiredInputFilterDoesAllowEmpty()
    {
        $field = $this->table->field('fruits');

        $this->assertTrue($field->getInputFilter()->allowEmpty());
    }

    public function testCanRetrieveOptionPairs()
    {
        $pairs = $this->table->field('fruits')->getOptionPairs()->fetch();

        $this->assertTrue(is_array($pairs));
        $this->assertEquals(2, count($pairs));
    }
}
