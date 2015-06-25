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
use WP_Session;

/**
 * A simple facade to session data
 */
class Session implements ArrayAccess
{
    /**
     * A \Dewdrop\Paths object for getting info about the application environment.
     * @var Paths
     */
    protected $paths;

    /**
     * The session data storage container appropriate for the current environment.
     *
     * @var \Symfony\Component\HttpFoundation\Session\Session|WP_Session
     */
    protected $container;

    /**
     * Provide a Pimple container for retrieval of session storage.
     *
     * @param PimpleProper $pimple
     */
    public function __construct(PimpleProper $pimple)
    {
        $this->container = $pimple['session'];
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
