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
 * A very basic bootstrap that can be used without requiring any additional
 * configuration or bootstrapping.
 */
class Standalone implements PimpleProviderInterface
{
    /**
     * @var Pimple
     */
    private $pimple;

    public function __construct()
    {
        $this->pimple = new Pimple();
    }

    /**
     * @return Pimple
     */
    public function getPimple()
    {
        return $this->pimple;
    }
}
