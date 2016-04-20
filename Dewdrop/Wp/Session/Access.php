<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Wp\Session;

use ArrayObject;
use Dewdrop\Exception;
use Dewdrop\Session\SessionAccessInterface;
use Recursive_ArrayAccess;
use WP_Session;

class Access implements SessionAccessInterface
{
    /**
     * @var ArrayObject|WP_Session
     */
    private $session;

    /**
     * Storage constructor.
     * @param $session
     */
    public function __construct($session)
    {
        if (!$session instanceof ArrayObject && !$session instanceof WP_Session) {
            throw new Exception('WP session storage requires either WP_Session or an ArrayObject placeholder.');
        }

        $this->session = $session;
    }

    public function get($name)
    {
        $out = $this->session[$name];
        
        if ($out instanceof Recursive_ArrayAccess) {
            $out = $out->toArray();
        }

        return $out;
    }

    public function set($name, $value)
    {
        $this->session[$name] = $value;

        return $this;
    }

    public function remove($name)
    {
        unset($this->session[$name]);

        return $this;
    }

    public function has($name)
    {
        return isset($this->session[$name]);
    }

    public function regenerateId()
    {
        if ($this->session instanceof WP_Session) {
            $this->session->regenerate_id();
        }
    }

}
