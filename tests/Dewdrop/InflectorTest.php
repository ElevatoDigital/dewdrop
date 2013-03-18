<?php

class Dewdrop_InflectorTest extends PHPUnit_Framework_TestCase
{
    protected $inflector;

    public function setUp()
    {
        $this->inflector = new \Dewdrop\Inflector();
    }

    public function testSimpleSingularize()
    {
        $this->assertEquals(
            'Fruit',
            $this->inflector->singularize('Fruits')
        );
    }
}

