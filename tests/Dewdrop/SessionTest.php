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
use Dewdrop\Session\SessionStorageInterface;
use PHPUnit_Framework_TestCase;
use Dewdrop\Wp\Session\Storage as WpSessionStorage;
use Pimple as PimpleProper;
use Zend_Session;

class SessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SessionStorageInterface
     */
    private $storage;

    /**
     * @var Session
     */
    private $session;

    public function setUp()
    {
        $this->storage = new WpSessionStorage(new ArrayObject());
        $this->session = new Session($this->storage);
    }

    public function testCanInstantiateWithAStorageImplementor()
    {
        $storage = new WpSessionStorage(new ArrayObject());
        $session = new Session($storage);

        $session->set('var', 'value');

        $this->assertEquals('value', $storage->get('var'));
    }

    public function testCanSetUsingArrayAccess()
    {
        $this->session['setArrayAccess'] = true;
        $this->assertTrue($this->storage->get('setArrayAccess'));
    }

    public function testCanSetWithMethodCall()
    {
        $this->session->set('setMethodCall', true);
        $this->assertTrue($this->storage->get('setMethodCall'));
    }

    public function testCanSetUsingMagicProperty()
    {
        $this->session->testMagicProperty = true;
        $this->assertTrue($this->storage->get('testMagicProperty'));
    }

    public function testCanGetUsingArrayAccess()
    {
        $this->storage->set('getArrayAccess', true);
        $this->assertTrue($this->session['getArrayAccess']);
    }

    public function testCanGetUsingMagicProperty()
    {
        $this->storage->set('getMagicProperty', true);
        $this->assertTrue($this->session->getMagicProperty);
    }

    public function testCanGetUsingMethodCall()
    {
        $this->storage->set('getMethodCall', true);
        $this->assertTrue($this->session->get('getMethodCall'));
    }

    public function testCanCheckPresenceWithArrayAccess()
    {
        $this->storage->set('arrayAccess', true);
        $this->assertTrue(isset($this->session['arrayAccess']));
    }

    public function testCanCheckPresenceWithMagicProperty()
    {
        $this->storage->set('magicProperty', true);
        $this->assertTrue(isset($this->session->magicProperty));
    }

    public function testCanCheckPresenceWithMethodCall()
    {
        $this->storage->set('methodCall', true);
        $this->assertTrue($this->session->has('methodCall'));
    }

    public function testCanUnsetWithArrayAccess()
    {
        $this->storage->set('arrayAccess', true);
        unset($this->session['arrayAccess']);
        $this->assertFalse($this->session->has('arrayAccess'));
    }

    public function testCanUnsetWithMagicProperty()
    {
        $this->storage->set('magicProperty', true);
        unset($this->session->magicProperty);
        $this->assertFalse($this->session->has('magicProperty'));
    }

    public function testCanUnsetWithMethodCall()
    {
        $this->storage->set('methodCall', true);
        $this->session->remove('methodCall');
        $this->assertFalse($this->session->has('methodCall'));
    }

    public function testCanRegenerateId()
    {
        $storage = $this->getMock(
            '\Dewdrop\Session\SessionStorageInterface'
        );

        $storage
            ->expects($this->once())
            ->method('regenerateId');

        $session = new Session($storage);
        $session->regenerateId();
    }

    public function testCanInstantiateWithNoArgsAndUseGlobalStorageResource()
    {
        $env = Env::getInstance();

        if ($env instanceof ZfEnv) {
            Zend_Session::$_unitTestEnabled = true;
        }

        $session = new Session();
        $globalStorage = Pimple::getResource('session.storage');
        $this->assertEquals($session->getStorage(), $globalStorage);
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testExceptionIsThrownWhenNoStorageInterfaceIsAvailable()
    {
        new Session(new \stdClass());
    }

    public function testCanProvideAPimpleObjectWithSessionStorageResource()
    {
        $pimple = new PimpleProper();

        $pimple['session.storage'] = $pimple->share(
            function () {
                return new WpSessionStorage(new ArrayObject());
            }
        );

        $session = new Session($pimple);

        $this->assertEquals($pimple['session.storage'], $session->getStorage());
    }
}
