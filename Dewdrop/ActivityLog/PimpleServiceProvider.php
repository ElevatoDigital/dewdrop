<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\ActivityLog;
use Pimple;

class PimpleServiceProvider
{
    public function register(Pimple $pimple)
    {
        $pimple['activity-log.db-gateway'] = $pimple->share(
            function () {
                return new DbGateway();
            }
        );

        $pimple['activity-log.handler-resolver'] = $pimple->share(
            function () use ($pimple) {
                return new HandlerResolver($pimple['paths']);
            }
        );

        $pimple['activity-log'] = $pimple->share(
            function () use ($pimple) {
                $dbGateway       = $pimple['activity-log.db-gateway'];
                $handlerResolver = $pimple['activity-log.handler-resolver'];
                return new ActivityLog($dbGateway, $handlerResolver);
            }
        );

        $pimple['activity-log.crud-message-templates'] = $pimple->share(
            function () use ($pimple) {
                return new CrudMessageTemplates($pimple['activity-log.handler-resolver']);
            }
        );
    }
}
