<?php

namespace Dewdrop\ActivityLog;

use Dewdrop\Db\Select;
use Dewdrop\Db\Table;

class DbGateway extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_activity_log');
    }

    public function insertEntry($summary, $message, array $entities)
    {
        $id = $this->insert(
            [
                'summary' => $summary,
                'message' => $message
            ]
        );

        foreach ($entities as $entity) {
            $entity['dewdrop_activity_log_id'] = $id;
            $this->getAdapter()->insert('dewdrop_activity_log_entities', $entity);
        }

        return $id;
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
            $select->limit($limit);
        }

        if ($offset) {
            $select->offset($offset);
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
}
