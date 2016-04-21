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
use Dewdrop\Env;
use Dewdrop\Env\EnvInterface;

/**
 * This class manages the basic Dewdrop configuration needed to get
 * things up and running.  For Dewdrop to run, it needs two pieces
 * of information at a minimum:
 *
 * 1) The Bootstrap class that will provide the Pimple depedency
 *    injection container.
 *
 * 2) The database configuration.
 *
 * In WP plugin projects, this information can be provided
 * automatically using information from the WordPress environment
 * itself.  In other projects, you'll have to provide a Bootstrap
 * class yourself.
 *
 * Note that you can use array access semantics with this object.
 */
class Config implements ArrayAccess
{
    /**
     * The configuration data.
     *
     * @var array
     */
    private $data = array();

    /**
     * Optionally point this class at a non-standard configuration file path.
     *
     * @param string $file
     */
    public function __construct(EnvInterface $env = null, $file = null)
    {
        $env = ($env ?: Env::getInstance());
        $this->data = $env->getConfigData($file);
    }

    /**
     * Get the named section from the configuration.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * Check to see if the named configuration section exists.
     *
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Set the value for the named configuration section.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Retrieve the named section from the configuration.
     *
     * @throws InvalidArgumentException
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $key));
        }

        return $this->data[$key];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $key The unique identifier for the config section
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $key The unique identifier for the parameter or object
     */
    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
