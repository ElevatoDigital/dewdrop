<?php

namespace Dewdrop\Fields\Filter;

use Dewdrop\Fields;
use Dewdrop\Fields\GroupedFields;
use Dewdrop\Pimple;
use PHPUnit_Framework_TestCase;

class GroupsTest extends PHPUnit_Framework_TestCase
{
    private $db;

    private $fields;

    private $filter;

    private $component = '/dewdrop-test/field-groups-component';

    public function setUp()
    {
        $this->db = Pimple::getResource('db');

        $this->filter = new Groups($this->component, $this->db);
        $this->filter->deleteCurrentSettings();

        $this->fields = new Fields();

        $this->fields
            ->add('one')
                ->setLabel('One')
            ->add('two')
                ->setLabel('Two')
            ->add('three')
                ->setLabel('Three')
            ->add('four')
                ->setLabel('Four');
    }

    public function testGettingConfigWithNothingInDatabaseReturnsUngroupedAndInCodedOrder()
    {
        $config = $this->filter->getConfigForFields($this->fields);

        $this->assertEquals(1, count($config));
        $this->assertEquals(4, count($config[Groups::UNGROUPED]['fields']));
        $this->assertEquals('one', $config[Groups::UNGROUPED]['fields'][0]['id']);
        $this->assertEquals('two', $config[Groups::UNGROUPED]['fields'][1]['id']);
        $this->assertEquals('three', $config[Groups::UNGROUPED]['fields'][2]['id']);
        $this->assertEquals('four', $config[Groups::UNGROUPED]['fields'][3]['id']);
    }

    public function testGettingConfigAfterSavingSortWithoutGroupsUsesCustomOrder()
    {
        $this->saveExampleUngroupedConfig();

        $config = $this->filter->getConfigForFields($this->fields);

        $this->assertEquals(1, count($config));
        $this->assertEquals(4, count($config[Groups::UNGROUPED]['fields']));
        $this->assertEquals('four', $config[Groups::UNGROUPED]['fields'][0]['id']);
        $this->assertEquals('three', $config[Groups::UNGROUPED]['fields'][1]['id']);
        $this->assertEquals('two', $config[Groups::UNGROUPED]['fields'][2]['id']);
        $this->assertEquals('one', $config[Groups::UNGROUPED]['fields'][3]['id']);
    }

    public function testGettingConfigAfterSavingSortWithGroupsReturnsSortedGroups()
    {
        $this->saveExampleGroupedConfig();

        $config = $this->filter->getConfigForFields($this->fields);

        // 3 groups
        $this->assertEquals(3, count($config));

        // 0 ungrouped
        $this->assertEquals(0, count($config[Groups::UNGROUPED]['fields']));

        // 2 in first group
        $this->assertEquals('four', $config[1]['fields'][0]['id']);
        $this->assertEquals('three', $config[1]['fields'][1]['id']);

        // 2 in first group
        $this->assertEquals('two', $config[2]['fields'][0]['id']);
        $this->assertEquals('one', $config[2]['fields'][1]['id']);
    }

    public function testApplyingFilterReturnsGroupedFields()
    {
        $fields = $this->filter->apply($this->fields);

        $this->assertInstanceOf('Dewdrop\Fields\GroupedFields', $fields);
    }

    public function testApplyingWithNoConfigReturnsFieldsInCodedOrderAndUngrouped()
    {
        $fields = $this->filter->apply($this->fields);

        $this->assertEquals(1, count($fields->getGroups()));
        $this->assertEquals(4, count(current($fields->getGroups())));

        $appliedIds = array();
        $codedIds   = array();

        foreach ($fields as $field) {
            $appliedIds[] = $field->getId();
        }

        foreach ($this->fields as $field) {
            $codedIds[] = $field->getId();
        }

        $this->assertEquals($codedIds, $appliedIds);
    }

    public function testApplyingWithGroupedConfigReturnsGroupedFieldsWithUngroupedLast()
    {
        $this->saveExampleGroupedConfig();

        $fields = $this->filter->apply($this->fields);
        $groups = $fields->getGroups();

        $this->assertEquals(2, count($groups[0]));
        $this->assertEquals('four', $groups[0]->getIterator()->current()->getId());

        $this->assertEquals(2, count($groups[1]));
        $this->assertEquals('two', $groups[1]->getIterator()->current()->getId());

        // No ungrouped fields
        $this->assertEquals(0, count($groups[2]));
    }

    public function testApplyingWithMixedConfigPutsUngroupedFieldsAtTheEnd()
    {
        $this->filter->save(
            array(
                array(
                    'title' => 'Ungrouped',
                    'fields' => array(
                        array('id' => 'four')
                    )
                ),
                array(
                    'title' => 'First Group',
                    'fields' => array(
                        array('id' => 'three')
                    )
                ),
                array(
                    'title' => 'Second Group',
                    'fields' => array(
                        array('id' => 'two'),
                        array('id' => 'one')
                    )
                ),
            )
        );

        $fields = $this->filter->apply($this->fields);

        $this->assertEquals('four', $fields->getGroups()[2]->getIterator()->current()->getId());
    }

    /**
     * @expectedException \Dewdrop\Fields\Exception
     */
    public function testSavingEmptyConfigThrowsException()
    {
        $this->filter->save(array());
    }

    /**
     * @expectedException \Dewdrop\Fields\Exception
     */
    public function testSavingUngroupedConfigWithoutFieldsThrowsException()
    {
        $this->filter->save(array(array()));
    }

    public function testNonExistentFieldInDbIsNotIncludedInConfig()
    {
        $this->filter->save(
            array(
                array(
                    'title' => 'Ungrouped',
                    'fields' => array(
                        array('id' => 'five'),
                        array('id' => 'four'),
                        array('id' => 'three'),
                        array('id' => 'two'),
                        array('id' => 'one')
                    )
                )
            )
        );

        $config = $this->filter->getConfigForFields($this->fields);
        $five   = null;

        foreach ($config[Groups::UNGROUPED]['fields'] as $field) {
            if ('five' === $field['id']) {
                $five = true;
            }
        }

        $this->assertNull($five);
    }

    private function saveExampleUngroupedConfig()
    {
        $this->filter->save(
            array(
                array(
                    'title' => 'Ungrouped',
                    'fields' => array(
                        array('id' => 'four'),
                        array('id' => 'three'),
                        array('id' => 'two'),
                        array('id' => 'one')
                    )
                )
            )
        );
    }

    private function saveExampleGroupedConfig()
    {
        $this->filter->save(
            array(
                array(
                    'title' => 'Ungrouped',
                    'fields' => array()
                ),
                array(
                    'title' => 'First Group',
                    'fields' => array(
                        array('id' => 'four'),
                        array('id' => 'three')
                    )
                ),
                array(
                    'title' => 'Second Group',
                    'fields' => array(
                        array('id' => 'two'),
                        array('id' => 'one')
                    )
                ),
            )
        );
    }
}

