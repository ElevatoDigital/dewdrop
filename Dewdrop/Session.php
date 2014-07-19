<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use ArrayAccess;
use Pimple as PimpleProper;
use Silex\Application as SilexApplication;
use Symfony\Component\HttpFoundation\Session\Session as SymfonySession;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use WP_Session;

/**
 * A simple facade to session data
 */
class Session implements ArrayAccess
{
    /**
     * @var Paths
     */
    protected $paths;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session|Wp_Session
     */
    protected $container;

    /**
     * @param PimpleProper $pimple
     * @return void
     */
    public function __construct(PimpleProper $pimple)
    {
        $this->paths = $pimple['paths'];

        if ($this->paths->isWp()) {
            $this->container = WP_Session::get_instance();
        } else {

            if (!isset($pimple['session.test'])) {
                $pimple['session.test'] = false;
            }

            if (!isset($pimple['session.storage.options'])) {
                $pimple['session.storage.options'] = [];
            }

            if (!isset($pimple['session.default_locale'])) {
                $pimple['session.default_locale'] = 'en';
            }

            if (!$pimple->offsetExists('session.storage.save_path')) {
                $pimple['session.storage.save_path'] = null;
            }

            $pimple['session.storage.handler'] = $pimple->share(function (PimpleProper $pimple) {
                return new NativeFileSessionHandler($pimple['session.storage.save_path']);
            });

            $pimple['session.storage.native'] = $pimple->share(function (PimpleProper $pimple) {
                return new NativeSessionStorage(
                    $pimple['session.storage.options'],
                    $pimple['session.storage.handler']
                );
            });

            $pimple['session.storage.test'] = $pimple->share(function () {
                return new MockFileSessionStorage();
            });

            if (!isset($pimple['session.storage'])) {
                if ($pimple['session.test']) {
                    $pimple['session.storage'] = $pimple['session.storage.test'];
                } else {
                    $pimple['session.storage'] = $pimple['session.storage.native'];
                }
            }

            $this->container = new SymfonySession($pimple['session.storage']);
        }
    }

    /**
     * Returns value for given name
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Returns whether value is set for given name
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * Sets name to the given value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Returns value for given name
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if ($this->container instanceof WP_Session) {
            $value = $this->container->offsetGet($name);
        } else {
            $value = $this->container->get($name);
        }

        return $value;
    }

    /**
     * Returns whether value is set for given name
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        if ($this->container instanceof WP_Session) {
            $result = $this->container->offsetExists($name);
        } else {
            $result = $this->container->has($name);
        }

        return $result;
    }

    /**
     * Whether a offset exists
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Regenerates the session ID
     *
     * @return void
     */
    public function regenerateId()
    {
        if ($this->container instanceof WP_Session) {
            $this->container->regenerate_id();
        } else {
            $this->container->migrate();
        }
    }

    /**
     * Removes the named value
     *
     * @param string $name
     * @return void
     */
    public function remove($name)
    {
        if ($this->container instanceof WP_Session) {
            $this->container->offsetUnset($name);
        } else {
            $this->container->remove($name);
        }
    }

    /**
     * Sets name to the given value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        if ($this->container instanceof WP_Session) {
            $this->container->offsetSet($name, $value);
        } else {
            $this->container->set($name, $value);
        }
    }
}
