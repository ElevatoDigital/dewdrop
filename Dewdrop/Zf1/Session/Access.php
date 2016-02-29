<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1\Session;

use Dewdrop\Session\SessionAccessInterface;
use Zend_Session_Namespace;
use Zend_Session;

class Access implements SessionAccessInterface
{
    private $sessionNamespace;

    public function __construct(Zend_Session_Namespace $sessionNamespace)
    {
        $this->sessionNamespace = $sessionNamespace;
    }

    public function get($name)
    {
        return $this->sessionNamespace->$name;
    }

    public function set($name, $value)
    {
        $this->sessionNamespace->$name = $value;

        return $this;
    }

    public function remove($name)
    {
        unset($this->sessionNamespace->$name);

        return $this;
    }

    public function has($name)
    {
        return isset($this->sessionNamespace->$name);
    }

    public function regenerateId()
    {
        Zend_Session::regenerateId();
    }
}
