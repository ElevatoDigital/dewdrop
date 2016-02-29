<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use ArrayObject;
use Dewdrop\Env;
use Dewdrop\Zf1\Env as ZfEnv;
use Dewdrop\Session\SessionAccessInterface;
use PHPUnit_Framework_TestCase;
use Dewdrop\Wp\Session\Access as WpSessionAccess;
use Pimple as PimpleProper;
use Zend_Session;

class SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SessionAccessInterface
     */
    private $access;

    /**
     * @var Session
     */
    private $session;

    public function setUp()
    {
        $this->access  = new WpSessionAccess(new ArrayObject());
        $this->session = new Session($this->access);
    }

    public function testCanInstantiateWithAStorageImplementor()
    {
        $storage = new WpSessionAccess(new ArrayObject());
        $session = new Session($storage);

        $session->set('var', 'value');

        $this->assertEquals('value', $storage->get('var'));
    }

    public function testCanSetUsingArrayAccess()
    {
        $this->session['setArrayAccess'] = true;
        $this->assertTrue($this->access->get('setArrayAccess'));
    }

    public function testCanSetWithMethodCall()
    {
        $this->session->set('setMethodCall', true);
        $this->assertTrue($this->access->get('setMethodCall'));
    }

    public function testCanSetUsingMagicProperty()
    {
        $this->session->testMagicProperty = true;
        $this->assertTrue($this->access->get('testMagicProperty'));
    }

    public function testCanGetUsingArrayAccess()
    {
        $this->access->set('getArrayAccess', true);
        $this->assertTrue($this->session['getArrayAccess']);
    }

    public function testCanGetUsingMagicProperty()
    {
        $this->access->set('getMagicProperty', true);
        $this->assertTrue($this->session->getMagicProperty);
    }

    public function testCanGetUsingMethodCall()
    {
        $this->access->set('getMethodCall', true);
        $this->assertTrue($this->session->get('getMethodCall'));
    }

    public function testCanCheckPresenceWithArrayAccess()
    {
        $this->access->set('arrayAccess', true);
        $this->assertTrue(isset($this->session['arrayAccess']));
    }

    public function testCanCheckPresenceWithMagicProperty()
    {
        $this->access->set('magicProperty', true);
        $this->assertTrue(isset($this->session->magicProperty));
    }

    public function testCanCheckPresenceWithMethodCall()
    {
        $this->access->set('methodCall', true);
        $this->assertTrue($this->session->has('methodCall'));
    }

    public function testCanUnsetWithArrayAccess()
    {
        $this->access->set('arrayAccess', true);
        unset($this->session['arrayAccess']);
        $this->assertFalse($this->session->has('arrayAccess'));
    }

    public function testCanUnsetWithMagicProperty()
    {
        $this->access->set('magicProperty', true);
        unset($this->session->magicProperty);
        $this->assertFalse($this->session->has('magicProperty'));
    }

    public function testCanUnsetWithMethodCall()
    {
        $this->access->set('methodCall', true);
        $this->session->remove('methodCall');
        $this->assertFalse($this->session->has('methodCall'));
    }

    public function testCanRegenerateId()
    {
        $storage = $this->getMock(
            '\Dewdrop\Session\SessionAccessInterface'
        );

        $storage
            ->expects($this->once())
            ->method('regenerateId');

        $session = new Session($storage);
        $session->regenerateId();
    }

    public function testCanInstantiateWithNoArgsAndUseGlobalAccessResource()
    {
        $env = Env::getInstance();

        if ($env instanceof ZfEnv) {
            Zend_Session::$_unitTestEnabled = true;
        }

        $session = new Session();
        $globalStorage = Pimple::getResource('session.access');
        $this->assertEquals($session->getAccessObject(), $globalStorage);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testExceptionIsThrownWhenNoAccessInterfaceIsAvailable()
    {
        new Session(new \stdClass());
    }

    public function testCanProvideAPimpleObjectWithSessionAccessResource()
    {
        $pimple = new PimpleProper();

        $pimple['session.access'] = $pimple->share(
            function () {
                return new WpSessionAccess(new ArrayObject());
            }
        );

        $session = new Session($pimple);

        $this->assertEquals($pimple['session.access'], $session->getAccessObject());
    }
}
