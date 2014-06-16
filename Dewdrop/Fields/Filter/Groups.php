<?php

namespace Dewdrop\Fields\Filter;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Fields;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\GroupedFields;

class Groups implements FilterInterface
{
    const UNGROUPED = 0;

    private $componentName;

    private $dbAdapter;

    private $fieldTableName = 'dewdrop_sorted_fields';

    private $groupTableName = 'dewdrop_field_groups';

    private $loadedDbData;

    public function __construct($componentName, DbAdapter $dbAdapter)
    {
        $this->componentName = $componentName;
        $this->dbAdapter     = $dbAdapter;
    }

    public function setFieldTableName($fieldTableName)
    {
        $this->fieldTableName = $fieldTableName;

        return $this;
    }

    public function setGroupTableName($groupTableName)
    {
        $this->groupTableName = $groupTableName;

        return $this;
    }

    public function getConfigForFields(Fields $fields)
    {
        $sortedList = $this->load();

        $config = array(
            array(
                'title'   => 'Ungrouped Fields',
                'fields'  => array(),
                'caption' => 'These fields have not been added to a group.  They\'ll automatically be sorted into
                    a group labeled "Other".',
            )
        );

        foreach ($sortedList as $fieldConfig) {
            if (!$fields->has($fieldConfig['field_id'])) {
                continue;
            }

            $field   = $fields->get($fieldConfig['field_id']);
            $groupId = (int) $fieldConfig['group_id'];

            if (!array_key_exists($groupId, $config)) {
                $config[$groupId] = array(
                    'title'   => $fieldConfig['group_title'],
                    'caption' => '',
                    'fields'  => array()
                );
            }

            $config[$groupId]['fields'][] = array(
                'id'    => $field->getId(),
                'label' => $field->getLabel()
            );
        }

        // Returning just the values to ensure we provide predictable numeric keys
        return array_values($config);
    }

    public function load()
    {
        $sql = $this->dbAdapter->quoteInto(
            'SELECT
                f.field_id,
                g.title AS group_title,
                g.dewdrop_field_group_id AS group_id
            FROM dewdrop_sorted_fields f
            LEFT JOIN dewdrop_field_groups g
                ON g.dewdrop_field_group_id = f.dewdrop_field_group_id
            WHERE f.component = ?
            ORDER BY
                f.dewdrop_field_group_id IS NULL DESC,
                g.sort_index,
                f.sort_index',
            $this->componentName
        );

        return $this->dbAdapter->fetchAll($sql);
    }

    public function save(array $groupConfig)
    {
        if (!count($groupConfig)) {
            throw new Exception('Invalid group configuration provided');
        } elseif (1 === count($groupConfig)) {
            $this->saveUngroupedList($groupConfig);
        } else {
            $this->saveGroups($groupConfig);
        }
    }

    protected function saveUngroupedList(array $groupConfig)
    {
        $this->dbAdapter->beginTransaction();

        $this->deleteCurrentSettings();

        $ungrouped = current($groupConfig);

        if (!isset($ungrouped['fields']) || !is_array($ungrouped['fields'])) {
            throw new Exception('No fields available in ungrouped list.');
        }

        $this->saveFields($ungrouped['fields'], null);

        $this->dbAdapter->commit();
    }

    protected function saveGroups(array $groupConfig)
    {
        $this->dbAdapter->beginTransaction();

        $this->deleteCurrentSettings();

        foreach ($groupConfig as $index => $group) {
            $groupId = null;

            if (self::UNGROUPED !== $index) {
                $this->dbAdapter->insert(
                    'dewdrop_field_groups',
                    array(
                        'title'      => $group['title'],
                        'sort_index' => $index
                    )
                );

                $groupId = $this->dbAdapter->lastInsertId();
            }

            $this->saveFields($group['fields'], $groupId);
        }

        $this->dbAdapter->commit();
    }

    protected function saveFields(array $fields, $groupId)
    {
        foreach ($fields as $index => $field) {
            $this->dbAdapter->insert(
                'dewdrop_sorted_fields',
                array(
                    'component'              => $this->componentName,
                    'field_id'               => $field['id'],
                    'sort_index'             => $index,
                    'dewdrop_field_group_id' => $groupId
                )
            );
        }

        return $this;
    }

    protected function deleteCurrentSettings()
    {
        $this->dbAdapter->delete(
            'dewdrop_field_groups',
            $this->dbAdapter->quoteInto(
                'dewdrop_field_group_id IN (
                    SELECT dewdrop_field_group_id
                    FROM dewdrop_sorted_fields
                    WHERE component = ?
                )',
                $this->componentName
            )
        );

        $this->dbAdapter->delete(
            'dewdrop_sorted_fields',
            $this->dbAdapter->quoteInto(
                'component = ?',
                $this->componentName
            )
        );
    }

    public function apply(Fields $currentFields)
    {
        if (!$this->loadedDbData) {
            $this->loadedDbData = $this->load();
        }

        $groupedFields = new GroupedFields();

        foreach ($this->loadedDbData as $fieldConfig) {
            // Ungrouped fields come after grouped fields
            if (!$fieldConfig['group_id']) {
                continue;
            }

            $fieldId = $fieldConfig['field_id'];
            $groupId = $fieldConfig['group_id'];

            if ($currentFields->has($fieldId)) {
                if (!$groupedFields->hasGroup($groupId)) {
                    $group = $groupedFields->addGroup($groupId);

                    $group->setTitle($fieldConfig['group_title']);
                }

                $groupedFields->getGroup($groupId)->add($currentFields->get($fieldId));
            }
        }

        $ungrouped = $groupedFields->addGroup('ungrouped');
        $ungrouped->setTitle('Other');

        foreach ($this->loadedDbData as $fieldConfig) {
            if (!$fieldConfig['group_id']) {
                $id = $fieldConfig['field_id'];

                if ($currentFields->has($id)) {
                    $ungrouped->add($currentFields->get($id));
                }
            }
        }

        foreach ($currentFields as $field) {
            if (!$groupedFields->has($field->getId())) {
                $ungrouped->add($field);
            }
        }

        return $groupedFields;
    }
}
