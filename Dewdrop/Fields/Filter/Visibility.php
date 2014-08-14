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
    protected $defaultFields = array();

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

    /**
     * The name of the DB table where user selections are stored.
     *
     * @var string
     */
    protected $dbTableName;

    /**
     * Provide the information needed to store selections in the database.
     * Typically, you won't need to specify the database table name, except
     * in testing.
     *
     * @param string $componentName
     * @param DbAdapter $dbAdapter
     * @param Fields $fields
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
        $this->defaultFields = array();

        foreach ($defaultFields as $field) {
            if ($field instanceof FieldInterface) {
                $field = $field->getId();
            }

            $this->defaultFields[] = $field;
        }

        return $this;
    }

    /**
     * Save the user's visible field selections back to the database.  We expect
     * the selections array to just contain field IDs.  Only those that match
     * IDs of fields in the supplied Fields object will be saved.
     *
     * @param Fields $fields
     * @param array $selections
     * @return void
     */
    public function save(Fields $fields, array $selections)
    {
        $visibleFields = array();

        foreach ($selections as $id) {
            if ($fields->has($id)) {
                $visibleFields[] = $id;
            }
        }

        if (count($visibleFields)) {
            $this->dbAdapter->beginTransaction();

            $this->dbAdapter->delete(
                $this->dbTableName,
                $this->dbAdapter->quoteInto(
                    'component = ?',
                    $this->componentName
                )
            );

            foreach ($visibleFields as $visibleFieldId) {
                $this->dbAdapter->insert(
                    $this->dbTableName,
                    array(
                        'component' => $this->componentName,
                        'field_id'  => $visibleFieldId
                    )
                );
            }

            $this->dbAdapter->commit();

            // Reset selections so they'll be loaded again when filter is re-applied
            $this->selections = null;
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
        $selections = $this->dbAdapter->fetchCol(
            sprintf(
                "SELECT field_id FROM %s WHERE component = ?",
                $this->dbAdapter->quoteIdentifier($this->dbTableName)
            ),
            array($this->componentName)
        );

        return is_array($selections) ? $selections : array();
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
            $defaults = array();

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
