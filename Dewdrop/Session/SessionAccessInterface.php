<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Session;

interface SessionAccessInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value);

    /**
     * @param string $name
     * @return $this
     */
    public function remove($name);

    /**
     * @param string $name
     * @return boolean
     */
    public function has($name);

    /**
     * @return void
     */
    public function regenerateId();
}
