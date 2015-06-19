<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Fields as FieldsSet;

/**
 * All fields implement this interface.  It covers 3 core field responsibilities:
 *
 * 1) Identification and labeling of fields.
 *
 * 2) Assignment and management of field helper callbacks for the field.
 *
 * 3) Management of the 4 core field permissions: visible, sortable, filterable,
 *    and editable.
 */
interface FieldInterface
{
    /**
     * Set the label that should be used when displaying this field.
     *
     * @param string $label
     * @return FieldInterface
     */
    public function setLabel($label);

    /**
     * Get the label that should be used when displaying this field.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set a note that will be displayed alongside this field when it is used
     * in a UI.
     *
     * @param string $note
     * @return FieldInterface
     */
    public function setNote($note);

    /**
     * Get the note associated with this field.
     *
     * @return string
     */
    public function getNote();

    /**
     * Set the ID that should be used to identify this field in Fields collections.
     *
     * @param string $id
     * @return FieldInterface
     */
    public function setId($id);

    /**
     * Get the ID that should be used to identify this field in Fields collections.
     *
     * @return string
     */
    public function getId();

    /**
     * Get an ID for this field that doesn't contain any special characters not
     * allowed in HTML ID attributes and/or CSS selectors.
     *
     * @return string
     */
    public function getHtmlId();

    /**
     * Get an ID for this field that doesn't contain any special characters not
     * allowed in URL query strings.
     *
     * @return string
     */
    public function getQueryStringId();

    /**
     * Set the FieldsSet that contains this FieldAbstract object.  Note that once
     * this is set, it cannot be changed without first calling resetFieldsSet().
     * This is done to ensure that when chaining calls between this field and its
     * containing set, the same set is always used.  This avoids inconsistent or
     * unpredictable cases where the field is added to one set but later customized
     * on a derivitive set.
     *
     * @param FieldsSet $fieldsSet
     * @return FieldAbstract
     */
    public function setFieldsSet(FieldsSet $fieldsSet);

    /**
     * When calling add() on this field, it will delegate the call back up to
     * the associated \Dewdrop\Fields object.  This allows for a very fluid
     * method chaining style when defining a large set of fields.
     *
     * @param mixed $field
     * @param string $modelName
     * @throws Exception
     * @return mixed
     */
    public function add($field, $modelName = null);

    /**
     * Assing a custom callback for use with the named field helper.
     *
     * @see \Dewdrop\Fields\Helper\HelperAbstract
     * @param string $helperName
     * @param callable $callable
     * @return FieldInterface
     */
    public function assignHelperCallback($helperName, $callable);

    /**
     * Check to see if this field has a custom callback defined for the
     * supplied helper name.
     *
     * @param string $helperName
     * @return boolean
     */
    public function hasHelperCallback($helperName);

    /**
     * Get the callback assigned to this field for the supplied helper
     * name.
     *
     * @param string $helperName
     * @return callable
     */
    public function getHelperCallback($helperName);

    /**
     * Get all helper callbacks that have been assigned to this field.
     * The returned array with have helper names as keys and the callables
     * themselves as values.  Mostly useful in debugging/introspection
     * contexts.
     *
     * @return array
     */
    public function getAllHelperCallbacks();

    /**
     * Set whether this field should be visible.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $visible
     * @return FieldInterface
     */
    public function setVisible($visible);

    /**
     * Get the current setting for this field's visibility.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getVisibleSetting();

    /**
     * Check to see if this field is visible.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isVisible(UserInterface $user = null);

    /**
     * Enable visibility for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setVisible() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function allowVisibilityForRole($role);

    /**
     * Forbid visibility for a specific role.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function forbidVisibilityForRole($role);

    /**
     * Set whether this field should be sortable.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $sortable
     * @return FieldInterface
     */
    public function setSortable($sortable);

    /**
     * Get the current setting for this field's sortability.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getSortableSetting();

    /**
     * Check to see if this field is sortable.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isSortable(UserInterface $user = null);

    /**
     * Enable sorting for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setSortable() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function allowSortingForRole($role);

    /**
     * Forbid sorting for a specific role.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function forbidSortingForRole($role);

    /**
     * Set whether this field should be filterable.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $filterable
     * @return FieldInterface
     */
    public function setFilterable($filterable);

    /**
     * Get the current setting for this field's filterability.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getFilterableSetting();

    /**
     * Check to see if this field is filterable.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isFilterable(UserInterface $user = null);

    /**
     * Enable filtering for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setFilterable() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function allowFilteringForRole($role);

    /**
     * Forbid filtering for a specific role.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function forbidFilteringForRole($role);

    /**
     * Set whether this field should be editable.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $editable
     * @return FieldInterface
     */
    public function setEditable($editable);

    /**
     * Get the current setting for this field's editability.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getEditableSetting();

    /**
     * Check to see if this field is editable.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isEditable(UserInterface $user = null);

    /**
     * Enable editing for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setEditable() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function allowEditingForRole($role);

    /**
     * Forbid editing for a specific role.
     *
     * @param mixed $role
     * @return FieldInterface
     */
    public function forbidEditingForRole($role);

    /**
     * Set a value for this field.
     *
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value);

    /**
     * Return the value for this field.
     *
     * @return mixed
     */
    public function getValue();
}
