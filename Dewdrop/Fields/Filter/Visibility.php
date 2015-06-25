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
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Pimple;
use Dewdrop\Db\Row;

/**
 * This filter will allow certain fields, which would otherwise be visible
 * based upon their actual definition/permissions, to be excluded in certain
 * contexts.  The most common use of this filter is to allow a user to select
 * which particular columns they'd like to be visible in a listing table.
 */
class Visibility implements FilterInterface
{
    /**
     * The fields (by ID) that should be included by default.
     *
     * @var array
     */
    protected $defaultFields = [];

    /**
     * The selections the user has made (stored in dewdrop_visible_fields).
     *
     * @var array
     */
    protected $selections;

    /**
     * The component name that should be used when storing user selections.
     *
     * @var string
     */
    protected $componentName;

    /**
     * The DB adapter used for DB access.
     *
     * @var DbAdapter
     */
    protected $dbAdapter;

    /**
     * The name of the DB table where user selections are stored.
     *
     * @var string
     */
    protected $dbTableName;

    /**
     * A DB row representing the currently logged-in user.
     *
     * @var Row
     */
    protected $user;

    /**
     * Provide the information needed to store selections in the database.
     * Typically, you won't need to specify the database table name, except
     * in testing.
     *
     * @param string $componentName
     * @param DbAdapter $dbAdapter
     * @param string $dbTableName
     */
    public function __construct(
        $componentName,
        DbAdapter $dbAdapter,
        $dbTableName = 'dewdrop_visible_fields'
    ) {
        $this->componentName = $componentName;
        $this->dbAdapter     = $dbAdapter;
        $this->dbTableName   = $dbTableName;
    }

    /**
     * Set the fields that should be displayed by default (if no user
     * selections have been made).  Your array can contain either
     * FieldInterface objects or string IDs for those fields.
     *
     * @param array|Fields $defaultFields
     * @return \Dewdrop\Fields\Filter\Visibility
     */
    public function setDefaultFields($defaultFields)
    {
        $this->defaultFields = [];

        foreach ($defaultFields as $field) {
            if ($field instanceof FieldInterface) {
                $field = $field->getId();
            }

            $this->defaultFields[] = $field;
        }

        return $this;
    }

    /**
     * Set the logged-in user that can be used when saving/loading per-user settings.
     *
     * @param Row $user
     * @return $this
     */
    public function setUser(Row $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the logged-in user row object for use when loading and saving per-user
     * filter settings.  Will attempt to retrieve this from Pimple if not set
     * explicitly.
     *
     * @return Row
     */
    public function getUser()
    {
        if (!$this->user && Pimple::hasResource('user')) {
            $user = Pimple::getResource('user');

            if ($user instanceof Row) {
                $this->user = $user;
            }
        }

        return $this->user;
    }

    /**
     * Detect whether this filter can be restricted to per-user settings or only
     * supports global settings.
     *
     * @return bool
     */
    public function canBeFilteredByUser()
    {
        return 0 < count($this->getUserReferenceValues());
    }

    /**
     * Save the user's visible field selections back to the database.  We expect
     * the selections array to just contain field IDs.  Only those that match
     * IDs of fields in the supplied Fields object will be saved.
     *
     * @param Fields $fields
     * @param array $selections
     * @param boolean $applyToAllUsers
     * @return void
     */
    public function save(Fields $fields, array $selections, $applyToAllUsers = false)
    {
        $visibleFields = [];

        foreach ($selections as $id) {
            if ($fields->has($id)) {
                $visibleFields[] = $id;
            }
        }

        if (count($visibleFields)) {
            $this->dbAdapter->beginTransaction();

            $this->deleteExistingValuesForSave($applyToAllUsers);

            foreach ($visibleFields as $visibleFieldId) {
                $data = [
                    'component' => $this->componentName,
                    'field_id'  => $visibleFieldId
                ];

                if (!$applyToAllUsers) {
                    foreach ($this->getUserReferenceValues() as $column => $value) {
                        $data[$column] = $value;
                    }
                }

                $this->dbAdapter->insert($this->dbTableName, $data);
            }

            $this->dbAdapter->commit();

            // Reset selections so they'll be loaded again when filter is re-applied
            $this->selections = null;
        }
    }

    /**
     * While saving, delete existing values.  If the new values will be limited to the
     * logged-in user, we only delete any settings specific to that user.  Otherwise,
     * we also delete any current values not specific to a user.
     *
     * @param boolean $applyToAllUsers
     */
    protected function deleteExistingValuesForSave($applyToAllUsers)
    {
        $where      = $this->dbAdapter->quoteInto('component = ?', $this->componentName);
        $references = $this->getUserReferenceValues();

        // We always delete per-user settings
        foreach ($references as $column => $value) {
            $where .= $this->dbAdapter->quoteInto(
                sprintf(' AND %s = ?', $this->dbAdapter->quoteIdentifier($column)),
                $value
            );
        }

        $this->dbAdapter->delete($this->dbTableName, $where);

        // If applying settings to all users, we _also_ delete the current global settings
        if ($applyToAllUsers) {
            $where = $this->dbAdapter->quoteInto('component = ?', $this->componentName);

            foreach ($references as $column => $value) {
                $where .= sprintf(' AND %s IS NULL', $this->dbAdapter->quoteIdentifier($column));
            }

            $this->dbAdapter->delete($this->dbTableName, $where);
        }
    }

    /**
     * Load the current selections from the database.  Will return an array
     * of field IDs.
     *
     * @return array
     */
    public function load()
    {
        $userRefs = $this->getUserReferenceValues();
        $select   = $this->selectForLoad();

        foreach ($userRefs as $column => $value) {
            $select->where(
                sprintf('%s = ?', $this->dbAdapter->quoteIdentifier($column)),
                $value
            );
        }

        $selections = $this->dbAdapter->fetchCol($select);

        // Fall back to global selections, if no per-user selections are available
        if (count($userRefs) && !count($selections)) {
            $select = $this->selectForLoad();

            foreach ($userRefs as $column => $value) {
                $select->where(sprintf('%s IS NULL', $this->dbAdapter->quoteIdentifier($column)));
            }

            $selections = $this->dbAdapter->fetchCol($select);
        }

        return is_array($selections) ? $selections : [];
    }

    /**
     * Create a Select object for use when loading filter settings from the DB.
     *
     * @return \Dewdrop\Db\Select
     */
    protected function selectForLoad()
    {
        return $this->dbAdapter->select()
            ->from($this->dbTableName, ['field_id'])
            ->where('component = ?', $this->componentName);
    }

    /**
     * Get the values for any foreign keys in the DB table that reference the user
     * object's table.  Allows the filter to be applied on a per-user basis.
     *
     * @return array
     */
    protected function getUserReferenceValues()
    {
        $user = $this->getUser();

        if (!$user) {
            return [];
        }

        $out = [];

        $userTable = $user->getTable();
        $metadata  = $this->dbAdapter->getTableMetadata($this->dbTableName);

        foreach ($metadata['references'] as $foreignKey => $reference) {
            if ($reference['table'] === $userTable->getTableName()) {
                $out[$foreignKey] = $user->get($reference['column']);
            }
        }

        return $out;
    }

    /**
     * Apply the field to the supplied set of fields.  If after filtering no
     * fields are left, we'll return the full set of fields as a fallback.
     * If no preferences are saved to the DB, we will use either pre-defined
     * defaults (@see setDefaults()) or the first 4 fields.
     *
     * @param Fields $fields
     * @return Fields
     */
    public function apply(Fields $fields)
    {
        if (!$this->selections) {
            $this->selections = $this->load();
        }

        if (0 !== count($this->defaultFields)) {
            $defaults = $this->defaultFields;
        } else {
            $defaults = [];

            foreach ($fields as $field) {
                if (4 > count($defaults)) {
                    $defaults[] = $field->getId();
                }
            }
        }

        $output = new Fields([], $fields->getUser());

        foreach ($fields as $field) {
            if (in_array($field->getId(), $this->selections) ||
                (!count($this->selections) && in_array($field->getId(), $defaults))
            ) {
                $output[] = $field;
            }
        }

        // If there are no fields found in the output, return the original fields array as a fallback
        if (!count($output)) {
            return $fields;
        }

        return $output;
    }
}
