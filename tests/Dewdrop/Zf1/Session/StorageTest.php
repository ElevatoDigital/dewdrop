<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1\Session;

use Dewdrop\Env;
use Dewdrop\Zf1\Env as ZfEnv;
use PHPUnit_Framework_TestCase;
use Zend_Session_Namespace;
use Zend_Session;

class StorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Session_Namespace
     */
    private $namespace;

    /**
     * @var Storage
     */
    private $storage;

    public function setUp()
    {
        $env = Env::getInstance();

        if (!$env instanceof ZfEnv) {
            $this->markTestSkipped('Zend session storage can only be tested in a Zend Framework 1 environment.');
        }

        Zend_Session::$_unitTestEnabled = true;
        Zend_Session::start();

        $this->namespace = new Zend_Session_Namespace('test');
        $this->storage   = new Storage($this->namespace);
    }

    public function testCanSetAValue()
    {
        $this->storage->set('var', 'value');
        $this->assertEquals('value', $this->namespace->var);
    }

    public function testCanGetAValue()
    {
        $this->namespace->test = 'value';
        $this->assertEquals('value', $this->storage->get('test'));
    }

    public function testCanCheckIfIndexIsSet()
    {
        $this->namespace->present = true;
        $this->assertTrue($this->storage->has('present'));
        $this->assertFalse($this->storage->has('notPresent'));
    }

    public function testCanUnsetAVariable()
    {
        $this->namespace->testUnset = 5;
        $this->assertEquals(5, $this->storage->get('testUnset'));
        $this->storage->remove('testUnset');
        $this->assertFalse(isset($this->namespace->testUnset));
        $this->assertFalse($this->storage->has('testUnset'));
    }
}
