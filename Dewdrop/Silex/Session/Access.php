<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Silex\Session;

use Dewdrop\Session\SessionAccessInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Access implements SessionAccessInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * Access constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->session->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->session->set($name, $value);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function remove($name)
    {
        $this->session->remove($name);

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return $this->session->has($name);
    }

    /**
     * @return void
     */
    public function regenerateId()
    {
        $this->session->migrate();
    }
}
