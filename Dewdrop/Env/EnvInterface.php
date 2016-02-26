<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Env;

use Pimple;

interface EnvInterface
{
    /**
     * @return boolean
     */
    public function isInUse();

    /**
     * @return array
     */
    public function getConfigData($file = null);

    /**
     * @return string
     */
    public function getBootstrapClass();

    /**
     * @return void
     */
    public function bootstrapCli();

    /**
     * @return void
     */
    public function initializeCli();

    /**
     * @param Pimple $pimple
     * @return void
     */
    public function initializePimple(Pimple $pimple);

    /**
     * @param Pimple $pimple
     * @return void
     */
    public function providePimpleSessionResource(Pimple $pimple);
}
