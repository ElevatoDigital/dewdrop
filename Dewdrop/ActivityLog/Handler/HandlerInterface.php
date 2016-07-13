<?php

namespace Dewdrop\ActivityLog\Handler;

use Dewdrop\ActivityLog;

interface HandlerInterface
{
    public function setActivityLog(ActivityLog $activityLog);

    public function write($summary, $message);

    public function log($summary, $message);

    public function getName();

    public function getFullyQualifiedName();

    public function getAliases();

    public function getIcon();

    public function renderTitleText($primaryKeyValue);

    public function renderLinkUrl($primaryKeyValue);

    public function createEntity($primaryKeyValue);
}
