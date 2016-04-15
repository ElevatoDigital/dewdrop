<?php

namespace Dewdrop\ActivityLog\Handler;

use Dewdrop\ActivityLog;
use Dewdrop\ActivityLog\Entity;

class NullHandler implements HandlerInterface
{
    /**
     * @var ActivityLog
     */
    private $activityLog;

    public function setActivityLog(ActivityLog $activityLog)
    {
        $this->activityLog = $activityLog;

        return $this;
    }

    public function write($summary, $message)
    {

    }

    public function log($summary, $message)
    {

    }

    public function getName()
    {
        return 'null';
    }

    public function getFullyQualifiedName()
    {
        return '/application/activity-log/null-handler';
    }

    public function getAliases()
    {
        return [];
    }

    public function __call($method, array $args)
    {
        // No-op on all handler methods for the null handler
    }

    public function getIcon()
    {
        return null;
    }

    public function renderTitleText($primaryKeyValue)
    {
        return 'null';
    }

    public function renderLinkUrl($primaryKeyValue)
    {
        return '#';
    }
    
    public function createEntity($primaryKeyValue)
    {
        return new Entity($this, $primaryKeyValue);
    }
}
