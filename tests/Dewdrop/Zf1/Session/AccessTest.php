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

class AccessTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Session_Namespace
     */
    private $namespace;

    /**
     * @var Access
     */
    private $access;

    public function setUp()
    {
        $env = Env::getInstance();

        if (!$env instanceof ZfEnv) {
            $this->markTestSkipped('Zend session storage can only be tested in a Zend Framework 1 environment.');
        }

        Zend_Session::$_unitTestEnabled = true;
        Zend_Session::start();

        $this->namespace = new Zend_Session_Namespace('test');
        $this->access    = new Access($this->namespace);
    }

    public function testCanSetAValue()
    {
        $this->access->set('var', 'value');
        $this->assertEquals('value', $this->namespace->var);
    }

    public function testCanGetAValue()
    {
        $this->namespace->test = 'value';
        $this->assertEquals('value', $this->access->get('test'));
    }

    public function testCanCheckIfIndexIsSet()
    {
        $this->namespace->present = true;
        $this->assertTrue($this->access->has('present'));
        $this->assertFalse($this->access->has('notPresent'));
    }

    public function testCanUnsetAVariable()
    {
        $this->namespace->testUnset = 5;
        $this->assertEquals(5, $this->access->get('testUnset'));
        $this->access->remove('testUnset');
        $this->assertFalse(isset($this->namespace->access));
        $this->assertFalse($this->access->has('testUnset'));
    }
}
