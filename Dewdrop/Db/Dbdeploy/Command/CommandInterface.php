<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy\Command;

/**
 * Basic "command" pattern interface.  May add some more to this
 * down the road.
 */
interface CommandInterface
{
    /**
     * Run primary command logic.  Return true on success.
     *
     * @return boolean
     */
    public function execute();
}
