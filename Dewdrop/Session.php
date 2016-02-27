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
use Dewdrop\Exception;
use Dewdrop\Session\SessionStorageInterface;
use Pimple as PimpleProper;

/**
 * A simple facade to session data.  You can use \Dewdrop\Session regardless of the
 * environment you're running in (i.e. WP, Silex, Zend Framework, etc).  It allows you
 * to manipulate session data using object properties, array access or explicit method
 * calls.  This makes it particularly important for Dewdrop core because you can write
 * your session code against \Dewdrop\Session and not worry about whether it will work
 * in the other supported environments.
 */
class Session implements ArrayAccess
{
    /**
     * The session data storage container appropriate for the current environment.
     *
     * @var SessionStorageInterface
     */
    protected $storage;

    /**
     * Provide a Pimple container for retrieval of session storage.
     *
     * @param mixed $container
     */
    public function __construct($storage = null)
    {
        if ($storage instanceof PimpleProper) {
            $storage = $storage['session.storage'];
        }

        if (null === $storage) {
            $storage = Pimple::getResource('session.storage');
        }

        if (!$storage instanceof SessionStorageInterface) {
            throw new Exception('Must provide a SessionStorageInterface object.');
        }

        $this->storage = $storage;
    }

    /**
     * Returns value for given name
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->storage->get($name);
    }

    /**
     * Returns whether value is set for given name
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->storage->has($name);
    }

    /**
     * Regenerates the session ID
     *
     * @return void
     */
    public function regenerateId()
    {
        $this->storage->regenerateId();
    }

    /**
     * Removes the named value
     *
     * @param string $name
     * @return $this
     */
    public function remove($name)
    {
        $this->storage->remove($name);

        return $this;
    }

    /**
     * Sets name to the given value
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->storage->set($name, $value);

        return $this;
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
     * Unset a value by calling unset() on an object property.
     *
     * @param $name
     */
    public function __unset($name)
    {
        return $this->remove($name);
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
     * Mostly provided to allow checking of storage setup during testing.
     *
     * @return SessionStorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
