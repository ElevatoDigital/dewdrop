<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

/**
 * A simple interface to identify objects implementing a save() method.
 */
interface SaveHandlerInterface
{
    /**
     * Perform any save logic required by the object.
     *
     * @return mixed
     */
    public function save();
}
