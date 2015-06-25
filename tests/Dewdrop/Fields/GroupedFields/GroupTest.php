<?php

namespace Dewdrop\Fields\GroupedFields;

use Dewdrop\Fields\GroupedFields;
use PHPUnit_Framework_TestCase;

class GroupTest extends PHPUnit_Framework_TestCase
{
    private $set;

    private $group;

    public function setUp()
    {
        $this->set   = new GroupedFields();
        $this->group = $this->set->addGroup('test');
    }

    public function testAddingAFieldToAGroupAddsItToTheSet()
    {
        $this->group->add('custom_field');
        $this->assertTrue($this->group->has('custom_field'));
        $this->assertTrue($this->set->has('custom_field'));
    }

    public function testRemoveAFieldFromAGroupRemovesItFromTheSet()
    {
        $this->group->add('custom');
        $this->group->remove('custom');

        $this->assertFalse($this->group->has('custom'));
        $this->assertFalse($this->set->has('custom'));
    }

    public function testCanSetAndGetTheTitleOfTheGroup()
    {
        $this->group->setTitle('TEST_TITLE');
        $this->assertEquals('TEST_TITLE', $this->group->getTitle());
    }
}
