<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Filter;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Fields;
use Dewdrop\Fields\Exception;
use Dewdrop\Fields\GroupedFields;

/**
 * Filter a set of fields into a \Dewdrop\Fields\GroupedFields object.
 * Components that understand the additional APIs provided by
 * \Dewdrop\Fields\GroupedFields object provides can then display their
 * fields in user-defined groups, while components that are unaware of
 * those APIs can continue using it just like a normal \Dewdrop\Fields
 * object that is sorted in a particular order.
 */
class Groups implements FilterInterface
{
    /**
     * A constant to help clarify that group zero is always index zero.
     *
     * @const
     */
    const UNGROUPED = 0;

    /**
     * The fully qualified component name to use when storing sorting and
     * grouping preferences in the DB.
     *
     * @var string
     */
    private $componentName;

    /**
     * The DB adapter that can be used for loading and saving preferences.
     *
     * @var DbAdapter
     */
    private $dbAdapter;

    /**
     * A variable where we can store the data loaded from the DB so that we
     * do not have to query for it multiple times on a single request.
     *
     * @var array
     */
    private $loadedDbData;

    /**
     * Provide a component name and DB adpater that can be used when saving
     * and loading preferences frmo the database.
     *
     * @param string $componentName
     * @param DbAdapter $dbAdapter
     */
    public function __construct($componentName, DbAdapter $dbAdapter)
    {
        $this->componentName = $componentName;
        $this->dbAdapter     = $dbAdapter;
    }

    /**
     * Get an array representation of the current preferences.  This is typically
     * used by a UI/form designed to allow the user to modify the grouping and
     * sorting preferences for a component.  The resulting array will be in the
     * following format:
     *
     * <pre>
     * array(
     *     'title'    => 'Group Title',
     *     'caption' => 'Group caption explaining what it is.  Usually only on unsorted group.',
     *     'fields'  => array(
     *         array(
     *             'id'    => 'products:name',
     *             'label' => 'Name'
     *         ),
     *         array(
     *             'id'    => 'products:price',
     *             'label' => 'Price'
     *         )
     *     )
     * )
     * </pre>
     *
     * @see \Dewdrop\Admin\Page\Stock\SortFields
     * @param Fields $fields
     * @return array
     */
    public function getConfigForFields(Fields $fields)
    {
        $sortedList  = $this->load();
        $fieldsAdded = array();

        $config = array(
            self::UNGROUPED => array(
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

            $fieldsAdded[] = $field->getId();
        }

        // Throw any fields there weren't matched into the "ungrouped" set
        foreach ($fields as $field) {
            if (!in_array($field->getId(), $fieldsAdded)) {
                $config[self::UNGROUPED]['fields'][] = array(
                    'id'    => $field->getId(),
                    'label' => $field->getLabel()
                );
            }
        }

        // Returning just the values to ensure we provide predictable numeric keys
        return array_values($config);
    }

    /**
     * Load the current preferences for this component from the database.  The
     * information will be returned in this format:
     *
     * <pre>
     * array(
     *     array(
     *         'field_id'    => 'products:name',
     *         'group_title' => 'My Field Group',
     *         'group_id'    => 2
     *     )
     * )
     * </pre>
     *
     * The fields and groups will be sorted when returned from this method, so
     * can iterate over it without having to first sort it.
     *
     * @return array
     */
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

    /**
     * Save the supplied configuration to the database.  We expect the info
     * to be provided in this format:
     *
     * <pre>
     * array(
     *     array(
     *         'title' => 'My Group Title',
     *         'fields' => array(
     *             array('id' => 'products:name'),
     *             array('id' => 'products:price')
     *         )
     *     )
     * )
     * </pre>
     *
     * @param array $groupConfig
     * @return Groups
     */
    public function save(array $groupConfig)
    {
        if (!count($groupConfig)) {
            throw new Exception('Invalid group configuration provided');
        } elseif (1 === count($groupConfig)) {
            $this->saveUngroupedList($groupConfig);
        } else {
            $this->saveGroups($groupConfig);
        }

        return $this;
    }

    /**
     * If there is only one group in the supplied config, we know that is the
     * "ungrouped" set of fields, which is always included.  So, we can save all
     * those fields with a null group ID.
     *
     * @param array $groupConfig
     * @return void
     */
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

    /**
     * Save multiple groups of fields to the database.
     *
     * @param array $groupConfig
     * @return void
     */
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

    /**
     * Save the fields to the DB using the supplied group ID, which can be null
     * in the case of un-grouped fields.
     *
     * @param array $fields
     * @param integer $groupId
     * @return void
     */
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
    }

    /**
     * Delete any existing settings from the database.  Done inside a transaction
     * while saving new settings.
     *
     * @return void
     */
    public function deleteCurrentSettings()
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

    /**
     * Apply the filter to the supplied set of fields.  In return, you'll end
     * up with a \Dewdrop\Fields\GroupedFields object that reflects the sort
     * order and grouping preferences contained in this filter.  You can use
     * that object either as a normal \Dewdrop\Fields object or you can call
     * getGroups() on it to get the fields back in their assigned groups.
     *
     * @param Fields $currentFields
     * @return GroupedFields
     */
    public function apply(Fields $currentFields)
    {
        if (!$this->loadedDbData) {
            $this->loadedDbData = $this->load();
        }

        $groupedFields = new GroupedFields([], $currentFields->getUser());

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
