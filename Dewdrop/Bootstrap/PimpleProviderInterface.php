<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Bootstrap;

use Pimple;

/**
 * Custom bootstrap classes just have to implement this interface.  Basically,
 * give Dewdrop a Pimple object.
 */
interface PimpleProviderInterface
{
    /**
     * Get the Pimple DI object that can be used to find common application
     * resources.
     *
     * @return Pimple
     */
    public function getPimple();
}
