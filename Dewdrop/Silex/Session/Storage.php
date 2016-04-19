<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Silex\Session;

use Dewdrop\Session\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Storage implements SessionStorageInterface
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get($name)
    {
        return $this->session->get($name);
    }

    public function set($name, $value)
    {
        $this->session->set($name, $value);

        return $this;
    }

    public function remove($name)
    {
        $this->session->remove($name);

        return $this;
    }

    public function has($name)
    {
        return $this->session->has($name);
    }

    public function regenerateId()
    {
        $this->session->migrate();
    }
}
