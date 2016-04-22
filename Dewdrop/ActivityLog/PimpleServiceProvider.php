<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\ActivityLog;
use Geocoder\Adapter\GeoIP2Adapter;
use Geocoder\Provider\GeoIP2 as GeoIp2Provider;
use GeoIp2\Database\Reader as GeoIp2Reader;
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

        $pimple['activity-log.user-information'] = $pimple->share(
            function () use ($pimple) {
                return new UserInformation(
                    $pimple['activity-log.db-gateway'],
                    $pimple['dewdrop-request'],
                    $pimple['activity-log.geocoder']
                );
            }
        );

        $pimple['activity-log.geocoder'] = $pimple->share(
            function () use ($pimple) {
                $paths  = $pimple['paths'];
                $dbFile = $paths->getData() . '/activity-log/GeoLite2.mmdb';

                if (!file_exists($dbFile) || !is_readable($dbFile)) {
                    return null;
                } else {
                    $reader   = new GeoIp2Reader($dbFile);
                    $adapter  = new GeoIP2Adapter($reader);
                    $geocoder = new GeoIp2Provider($adapter);
                    return $geocoder;
                }
            }
        );

        $pimple['activity-log'] = $pimple->share(
            function () use ($pimple) {
                $dbGateway       = $pimple['activity-log.db-gateway'];
                $handlerResolver = $pimple['activity-log.handler-resolver'];
                $userInformation = $pimple['activity-log.user-information'];
                return new ActivityLog($dbGateway, $handlerResolver, $userInformation);
            }
        );

        $pimple['activity-log.crud-message-templates'] = $pimple->share(
            function () use ($pimple) {
                return new CrudMessageTemplates($pimple['activity-log.handler-resolver']);
            }
        );
    }
}
