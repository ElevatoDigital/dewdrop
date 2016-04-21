<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Silex\Session;

use Dewdrop\Env;
use Dewdrop\Silex\Env as SilexEnv;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;


class AccessTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var Access
     */
    private $access;

    public function setUp()
    {
        $env = Env::getInstance();

        if (!$env instanceof SilexEnv) {
            $this->markTestSkipped('Silex session storage can only be tested in a Silex environment.');
        }

        $this->session = new Session(new MockArraySessionStorage());
        $this->access  = new Access($this->session);
    }

    public function testCanSetAValue()
    {
        $this->access->set('var', 'value');
        $this->assertEquals('value', $this->session->get('var'));
    }

    public function testCanGetAValue()
    {
        $this->session->set('test', 'value');
        $this->assertEquals('value', $this->access->get('test'));
    }

    public function testCanCheckIfIndexIsSet()
    {
        $this->session->set('present', true);
        $this->assertTrue($this->access->has('present'));
        $this->assertFalse($this->access->has('notPresent'));
    }

    public function testCanUnsetAVariable()
    {
        $this->session->set('testUnset', 5);
        $this->assertEquals(5, $this->access->get('testUnset'));
        $this->access->remove('testUnset');
        $this->assertFalse($this->session->has('testUnset'));
        $this->assertFalse($this->access->has('testUnset'));
    }
}
