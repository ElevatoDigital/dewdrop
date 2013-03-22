<?php

use Dewdrop\Inflector;

class Dewdrop_InflectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Dewdrop\Inflector
     */
    protected $inflector;

    public function setUp()
    {
        $this->inflector = new Inflector();
    }

    public function testSimpleSingularize()
    {
        $this->assertEquals(
            'Fruit',
            $this->inflector->singularize('Fruits')
        );
    }

    public function testUnaccentSafeForUtf8()
    {
        $this->assertEquals('Z', $this->inflector->unaccent('Ž'));
    }
}

