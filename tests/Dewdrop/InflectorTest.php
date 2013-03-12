<?php

require_once dirname(__DIR__) . '/bootstrap.php';

class Dewdrop_InflectorTest extends PHPUnit_Framework_TestCase
{
    protected $inflector;

    public function setUp()
    {
        $this->inflector = new \Dewdrop\Inflector();
    }

    public function testComponentClassPath()
    {
        $base = dirname(dirname(dirname(__DIR__))) . '/admin';
        $name = 'test';

        $this->assertEquals(
            "$base/test/Component.php",
            $this->inflector->getComponentClassPath($name)
        );
    }

    public function testComponentClass()
    {
        $name = 'test';

        $this->assertEquals(
            "\\Admin\\Test\\Component",
            $this->inflector->getComponentClass($name)
        );
    }

    public function testMultiWordComponentClass()
    {
        $name = 'multi-word-test';

        $this->assertEquals(
            "\\Admin\\MultiWordTest\\Component",
            $this->inflector->getComponentClass($name)
        );
    }

    public function testModelClassPath()
    {
        $base = dirname(dirname(dirname(__DIR__))) . '/models';
        $name = 'Fruits';

        $this->assertEquals(
            "$base/$name.php",
            $this->inflector->getModelClassPath($name)
        );
    }

    public function testModelClass()
    {
        $this->assertEquals(
            '\Model\Fruits',
            $this->inflector->getModelClass('Fruits')
        );
    }
}
