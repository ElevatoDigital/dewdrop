<?php

namespace Dewdrop;

use Dewdrop\Test\BaseTestCase;

class PathsTest extends BaseTestCase
{
    protected $paths;

    protected $root;

    protected $pluginRoot;

    public function setUp()
    {
        $this->pluginRoot = realpath(__DIR__ . '/../../../../..');
        $this->paths      = new Paths();

        if (defined('ABSPATH')) {
            $this->root = rtrim(ABSPATH, '/');
        } else {
            $this->root = $this->pluginRoot;
        }
    }

    public function testRoot()
    {
        $this->assertEquals($this->root, $this->paths->getRoot());
    }

    public function testDewdropLib()
    {
        $this->assertEquals(
            realpath(__DIR__ . '/../../'),
            $this->paths->getDewdropLib()
        );
    }

    public function testPluginRoot()
    {
        $this->assertEquals($this->pluginRoot, $this->paths->getPluginRoot());
    }

    public function testAdmin()
    {
        $this->assertEquals($this->pluginRoot . '/admin', $this->paths->getAdmin());
    }

    public function testDb()
    {
        $this->assertEquals($this->pluginRoot . '/db', $this->paths->getDb());
    }

    public function testLib()
    {
        $this->assertEquals($this->pluginRoot . '/lib', $this->paths->getLib());
    }

    public function testModels()
    {
        $this->assertEquals($this->pluginRoot . '/models', $this->paths->getModels());
    }

    public function testShortCodes()
    {
        $this->assertEquals($this->pluginRoot . '/short-codes', $this->paths->getShortCodes());
    }

    public function testTests()
    {
        $this->assertEquals($this->pluginRoot . '/tests', $this->paths->getTests());
    }
}
