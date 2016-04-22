<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\Db\Select;
use Dewdrop\Db\Table;
use Geocoder\Model\Address as GeocoderResult;
use Jenssegers\Agent\Agent as UserAgent;

class DbGateway extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_activity_log');
    }

    public function insertEntry($summary, $message, array $entities, $userInfoId = null)
    {
        $data = [
            'summary' => $summary,
            'message' => $message,
        ];

        if ($userInfoId) {
            $data['dewdrop_activity_log_user_information_id'] = $userInfoId;
        }

        $id = $this->insert($data);

        foreach ($entities as $entity) {
            $entity['dewdrop_activity_log_id'] = $id;
            $this->getAdapter()->insert('dewdrop_activity_log_entities', $entity);
        }

        return $id;
    }

    public function insertUserInformation(
        $ipAddress,
        $userAgentString,
        $sapiName,
        GeocoderResult $geocoderResult = null
    ) {
        $userAgent = new UserAgent();
        $userAgent->setUserAgent($userAgentString);

        $browser  = $userAgent->browser();
        $platform = $userAgent->platform();

        $data = [
            'ip_address'                  => $ipAddress,
            'sapi_name'                   => substr($sapiName, 0, 32),
            'user_agent'                  => substr($userAgentString, 0, 255),
            'user_agent_browser'          => substr($browser, 0, 32),
            'user_agent_device'           => substr($userAgent->device(), 0, 32),
            'user_agent_browser_version'  => substr($userAgent->version($browser), 0, 32),
            'user_agent_platform_version' => substr($userAgent->version($platform), 0, 32),
            'user_agent_platform'         => substr($platform, 0, 32),
            'user_agent_robot'            => substr($userAgent->robot(), 0, 32)
        ];

        if ($geocoderResult) {
            $region = $geocoderResult->getAdminLevels()->first();

            $data['geo_city']         = substr($geocoderResult->getLocality(), 0, 32);
            $data['geo_region']       = ($region ? substr($region->getCode(), 0, 32) : null);
            $data['geo_country']      = substr($geocoderResult->getCountry(), 0, 32);
            $data['geo_country_code'] = substr($geocoderResult->getCountryCode(), 0, 6);
            $data['geo_latitude']     = substr($geocoderResult->getLatitude(), 0, 32);
            $data['geo_longitude']    = substr($geocoderResult->getLongitude(), 0, 32);
        }

        $this->getAdapter()->insert('dewdrop_activity_log_user_information', $data);

        return $this->getAdapter()->lastInsertId();
    }

    public function selectEntries(array $handlers, array $entities, $limit, $offset, $order)
    {
        $select = $this->select();

        $select->from('dewdrop_activity_log');

        if (count($handlers)) {
            $select->where(
                'dewdrop_activity_log_id IN (
                    SELECT dewdrop_activity_log_id
                    FROM dewdrop_activity_log_entities
                    WHERE handler IN (?)
                )',
                $handlers
            );
        }

        if (count($entities)) {
            $select->registerConditionSet('entities', Select::SQL_OR);

            /* @var $entity Entity */
            foreach ($entities as $entity) {
                $select->whereConditionSet(
                    'entities',
                    sprintf(
                        'dewdrop_activity_log_id IN (
                            SELECT dewdrop_activity_log_id
                            FROM dewdrop_activity_log_entities
                            WHERE %s AND %s
                        )',
                        $this->getAdapter()->quoteInto('handler = ?', $entity->getFullyQualifiedName()),
                        $this->getAdapter()->quoteInto('primary_key_value = ?', $entity->getPrimaryKeyValue())
                    )
                );
            }
        }

        if ($limit) {
            $select->limit($limit, $offset);
        }

        $select->order("date_created {$order}");

        return $select;
    }

    public function fetchEntitiesForEntries(array $entries)
    {
        $ids = [];

        foreach ($entries as $entry) {
            $ids[] = $entry['dewdrop_activity_log_id'];
        }

        $rows = $this->getAdapter()->fetchAll(
            $this->getAdapter()->quoteInto(
                'SELECT dewdrop_activity_log_id, handler, primary_key_value AS id, title_text AS title
                FROM dewdrop_activity_log_entities
                WHERE dewdrop_activity_log_id IN (?)',
                $ids
            )
        );

        $out = [];

        foreach ($rows as $row) {
            $id = $row['dewdrop_activity_log_id'];

            if (!array_key_exists($id, $out)) {
                $out[$id] = [];
            }

            $out[$id][] = [
                'handler' => $row['handler'],
                'id'      => $row['id'],
                'title'   => $row['title']
            ];
        }

        return $out;
    }

    public function fetchUserInformationForEntries(array $entries)
    {
        $ids = [];

        foreach ($entries as $entry) {
            $id = $entry['dewdrop_activity_log_user_information_id'];

            if ($id && !in_array($id, $ids)) {
                $ids[] = $id;
            }
        }

        if (!count($ids)) {
            return [];
        }

        return $this->getAdapter()->fetchAll(
            $this->getAdapter()->quoteInto(
                'SELECT * FROM dewdrop_activity_log_user_information
                WHERE dewdrop_activity_log_user_information_id IN (?)',
                $ids
            )
        );
    }
}
