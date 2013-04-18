<?php

namespace Dewdrop;

use Dewdrop\Test\BaseTestCase;

class InflectorTest extends BaseTestCase
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

