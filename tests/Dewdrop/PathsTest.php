<?php

namespace Dewdrop;

use Dewdrop\Test\BaseTestCase;

class PathsTest extends BaseTestCase
{
    protected $paths;

    protected $wpRoot;

    protected $pluginRoot;

    public function setUp()
    {
        $this->paths      = new Paths();
        $this->wpRoot     = realpath(__DIR__ . '/../../../../../../');
        $this->pluginRoot = realpath(__DIR__ . '/../../../');
    }

    public function testWpRoot()
    {
        $this->assertEquals($this->wpRoot, $this->paths->getWpRoot());
    }

    public function testDewdropLib()
    {
        $this->assertEquals($this->pluginRoot . '/vendor/Dewdrop', $this->paths->getDewdropLib());
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
