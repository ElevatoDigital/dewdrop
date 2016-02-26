<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1;

use Dewdrop\Paths as CorePaths;

class Paths extends CorePaths
{
    public function getModels()
    {
        return $this->getAppRoot() . '/application/models';
    }
}
