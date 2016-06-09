<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\ActivityLog;
use Dewdrop\ActivityLog\Entity;

class ActivityLogEntity extends AbstractHelper
{
    public function direct(Entity $entity)
    {
        return $this->partial(
            'activity-log-entity.phtml',
            ['entity' => $entity]
        );
    }
}
