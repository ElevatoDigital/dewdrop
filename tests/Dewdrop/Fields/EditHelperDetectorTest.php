<?php

namespace Dewdrop\Fields;

use Dewdrop\Test\BaseTestCase;

class EditHelperDetectorTest extends BaseTestCase
{
    private $detector;

    public function setUp()
    {
        $this->detector = new EditHelperDetector();
    }

    public function testCanCustomizeValueForAGivenField()
    {
        $field = $this->getTestField();

        $this->detector->customizeField($field, 'inputCheckbox');

        $this->assertEquals('inputCheckbox', $this->detector->detect($field));
    }

    public function testForeignKeyReferenceWillUseWpSelectByDefault()
    {
        $field = $this->getTestField('reference');

        $this->assertEquals('select', $this->detector->detect($field));
    }

    public function testManyToManyWillUseCheckboxListByDefault()
    {
        $field = $this->getTestField('manytomany');

        $this->assertEquals('checkboxList', $this->detector->detect($field));
    }

    public function testStringFieldWillUseInputTextByDefault()
    {
        $field = $this->getTestField('text');

        $this->assertEquals('inputText', $this->detector->detect($field));
    }

    public function testClobFieldWillUseTextareaByDefault()
    {
        $field = $this->getTestField('clob');

        $this->assertEquals('textarea', $this->detector->detect($field));
    }

    public function testBooleanFieldWillUseCheckboxByDefault()
    {
        $field = $this->getTestField('boolean');

        $this->assertEquals('inputCheckbox', $this->detector->detect($field));
    }

    public function testEavFieldWillUseDefinedEditHelper()
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/test-tables/DewdropTestFruits.php';
        $table = new \DewdropFieldsTest\DewdropTestFruits($db);
        $row   = $table->createRow();

        $field = $this->getMock(
            '\Dewdrop\Db\Eav\Field',
            array('getEditHelperName'),
            array($table, 'name', $table->getMetadata('columns', 'name'))
        );

        $field
            ->expects($this->once())
            ->method('getEditHelperName')
            ->will($this->returnValue('inputCheckbox'));

        $this->assertEquals('inputCheckbox', $this->detector->detect($field));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testDetectingUnknownTypeWillThrowException()
    {
        $field = $this->getTestField('fafafafa');

        $this->detector->detect($field);
    }

    private function getTestField($type = null)
    {
        $db = new \Dewdrop\Db\Adapter\Mock();

        require_once __DIR__ . '/test-tables/DewdropTestFruits.php';
        $table = new \DewdropFieldsTest\DewdropTestFruits($db);
        $row   = $table->createRow();

        if (null === $type) {
            $field = $row->field('name');
        } else {
            $field = $this->getMock(
                '\Dewdrop\Db\Field',
                array('isType'),
                array($table, 'name', $table->getMetadata('columns', 'name'))
            );

            $field
                ->expects($this->any())
                ->method('isType')
                ->will(
                    $this->returnCallback(
                        function () use ($type) {
                            foreach (func_get_args() as $arg) {
                                if ($arg === $type) {
                                    return true;
                                }
                            }

                            return false;
                        }
                    )
                );
        }

        return $field;
    }
}
