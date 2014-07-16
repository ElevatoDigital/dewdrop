<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

use Dewdrop\Fields;
use Dewdrop\Fields as FieldsSet;
use Dewdrop\Fields\Exception;

/**
 * This class serves as a base for both custom and DB fields in Dewdrop.
 * It implements the helper callback assignment and permissions management
 * of FieldInterface, leaving the ID/label handling up to the sub-classes
 * (i.e. \Dewdrop\Db\Field and \Dewdrop\Fields\Field).  ID/label handling
 * differs quite a lot in the 2 primary types of fields.  For DB fields,
 * sane default are available from the DB metadata, whereas for custom
 * fields, all this information must be defined manually.
 *
 * Note that in the case of custom fields, all capabilities (visibility,
 * sortability, filterability, and editability) are forbidden by default.
 * In DB fields, on the other hand, these capabilities are all enabled
 * by default.  Similarly the situation with ID/label handling, we do
 * this because database fields can provide sane default by leveraging
 * information from the schema.  Custom fields, though, need to be
 * thought out and configured manually.
 *
 * To support the storage of the permissions settings for this field,
 * a scheme using arrays is deployed:
 *
 * 1) If the permissions/capability array contains only
 *    \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL, it is
 *    allowed for everyone.
 *
 * 2) If it contains nothing, it is forbidden for everyone.
 *
 * 3) Otherwise, it will contains one ore more "roles" which can be
 *    any value that can be passed along to the UserInterface object
 *    to see if the current user can use that capability.
 */
abstract class FieldAbstract implements FieldInterface
{
    /**
     * A placeholder used to signify that a capability is available
     * to everyone, even if no UserInterface object is supplied to
     * test against.
     *
     * @const
     */
    const AUTHORIZATION_ALLOW_ALL = 'authorization:allow:all';

    /**
     * Any notes that should be displayed along with this field when it is
     * displayed to users.
     *
     * @var string
     */
    protected $note = '';

    /**
     * The Fields object this field is associated with.  This is really
     * only used to enable our streamlined method chaining.
     *
     * @var Fields
     */
    protected $fieldsSet;

    /**
     * The setting for this field's visibility.  See the top-level docblock
     * for this class for info on how this is stored.
     *
     * @var array
     */
    protected $visible = array();

    /**
     * The setting for this field's sortability.  See the top-level docblock
     * for this class for info on how this is stored.
     *
     * @var array
     */
    protected $sortable = array();

    /**
     * The setting for this field's filterability.  See the top-level docblock
     * for this class for info on how this is stored.
     *
     * @var array
     */
    protected $filterable = array();

    /**
     * The setting for this field's editability.  See the top-level docblock
     * for this class for info on how this is stored.
     *
     * @var array
     */
    protected $editable = array();

    /**
     * Any custom helper callbacks assigned to this field.
     *
     * @var array
     */
    protected $customHelperCallbacks = array();

    /**
     * Set a note that will be displayed alongside this field when it is used
     * in a UI.
     *
     * @param string $note
     * @return FieldAbstract
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get the note associated with this field.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set multiple options at once on this field using an array of
     * option names and their values.
     *
     * @param array $options
     * @throws Exception
     * @return FieldAbstract
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);

            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                throw new Exception("Setting unknown option '{$name}'");
            }
        }

        return $this;
    }

    /**
     * Can be used to break the association between this field and the set it
     * was originally added to so that it can be associated with a different
     * set.
     *
     * @return FieldAbstract
     */
    public function resetFieldsSet()
    {
        $this->fieldsSet = null;

        return $this;
    }

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
    public function setFieldsSet(FieldsSet $fieldsSet)
    {
        if ($this->fieldsSet) {
            return $this;
        }

        $this->fieldsSet = $fieldsSet;

        return $this;
    }

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
    public function add($field, $modelName = null)
    {
        if (!$this->fieldsSet) {
            throw new Exception('Cannot add field because no \Dewdrop\Fields object is available');
        }

        return $this->fieldsSet->add($field, $modelName);
    }

    /**
     * Set whether this field should be visible.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $visible
     * @return FieldAbstract
     */
    public function setVisible($visible)
    {
        return $this->setPermission('visible', $visible);
    }

    /**
     * Get the current setting for this field's visibility.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getVisibleSetting()
    {
        return $this->visible;
    }

    /**
     * Check to see if this field is visible.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isVisible(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('visible', $user);
    }

    /**
     * Enable visibility for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setVisible() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function allowVisibilityForRole($role)
    {
        return $this->allowPermissionForRole('visible', $role);
    }

    /**
     * Forbid visibility for a specific role.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function forbidVisibilityForRole($role)
    {
        return $this->forbidPermissionForRole('visible', $role);
    }

    /**
     * Set whether this field should be sortable.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $sortable
     * @return FieldAbstract
     */
    public function setSortable($sortable)
    {
        return $this->setPermission('sortable', $sortable);
    }

    /**
     * Get the current setting for this field's sortability.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getSortableSetting()
    {
        return $this->sortable;
    }

    /**
     * Check to see if this field is sortable.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isSortable(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('sortable', $user);
    }

    /**
     * Enable sorting for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setSortable() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function allowSortingForRole($role)
    {
        return $this->allowPermissionForRole('sortable', $role);
    }

    /**
     * Forbid sorting for a specific role.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function forbidSortingForRole($role)
    {
        return $this->forbidPermissionForRole('sortable', $role);
    }

    /**
     * Set whether this field should be filterable.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $filterable
     * @return FieldAbstract
     */
    public function setFilterable($filterable)
    {
        return $this->setPermission('filterable', $filterable);
    }

    /**
     * Get the current setting for this field's filterability.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getFilterableSetting()
    {
        return $this->filterable;
    }

    /**
     * Check to see if this field is filterable.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isFilterable(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('filterable', $user);
    }

    /**
     * Enable filtering for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setFilterable() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function allowFilteringForRole($role)
    {
        return $this->allowPermissionForRole('filterable', $role);
    }

    /**
     * Forbid filtering for a specific role.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function forbidFilteringForRole($role)
    {
        return $this->forbidPermissionForRole('filterable', $role);
    }

    /**
     * Set whether this field should be editable.  Can supply either a
     * boolean, in which case true means globally allowed and false
     * means globally forbidden, or an array of roles/capabilities
     * for which it is allowed.
     *
     * @param mixed $editable
     * @return FieldAbstract
     */
    public function setEditable($editable)
    {
        return $this->setPermission('editable', $editable);
    }

    /**
     * Get the current setting for this field's editability.  Will
     * return an empty array if completely forbidden, an array containing
     * only \Dewdrop\Fields\FieldAbstract::AUTHORIZATION_ALLOW_ALL if
     * completely allowed, or an array containing 1 or more roles if
     * a custom setting is applied.
     *
     * @return array
     */
    public function getEditableSetting()
    {
        return $this->editable;
    }

    /**
     * Check to see if this field is editable.  If no user is supplied,
     * this method will only return true when the field is visible
     * globally.  Otherwise, it will check to see if the user has
     * a matching role/capability.
     *
     * @param UserInterface $user
     * @return boolean
     */
    public function isEditable(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('editable', $user);
    }

    /**
     * Enable editing for a specific role.  You can call this
     * multiple times to configure the field for all roles, or call
     * setEditable() once with an array of roles.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function allowEditingForRole($role)
    {
        return $this->allowPermissionForRole('editable', $role);
    }

    /**
     * Forbid editing for a specific role.
     *
     * @param mixed $role
     * @return FieldAbstract
     */
    public function forbidEditingForRole($role)
    {
        return $this->forbidPermissionForRole('editable', $role);
    }

    /**
     * Assing a custom callback for use with the named field helper.
     *
     * @see \Dewdrop\Fields\Helper\HelperAbstract
     * @param string $helperName
     * @param callable $callable
     * @return FieldAbstract
     */
    public function assignHelperCallback($helperName, $callable)
    {
        $this->customHelperCallbacks[strtolower($helperName)] = $callable;

        return $this;
    }

    /**
     * Check to see if this field has a custom callback defined for the
     * supplied helper name.
     *
     * @param string $helperName
     * @return boolean
     */
    public function hasHelperCallback($helperName)
    {
        return array_key_exists(strtolower($helperName), $this->customHelperCallbacks);
    }

    /**
     * Get the callback assigned to this field for the supplied helper
     * name.
     *
     * @param string $helperName
     * @return callable
     */
    public function getHelperCallback($helperName)
    {
        return $this->customHelperCallbacks[strtolower($helperName)];
    }

    /**
     * Get all helper callbacks that have been assigned to this field.
     * The returned array with have helper names as keys and the callables
     * themselves as values.  Mostly useful in debugging/introspection
     * contexts.
     *
     * @return array
     */
    public function getAllHelperCallbacks()
    {
        return $this->customHelperCallbacks;
    }

    /**
     * Allow the permission for the specified role.
     *
     * @param string $permissionProperty
     * @param mixed $role
     * @return FieldAbstract
     */
    private function allowPermissionForRole($permissionProperty, $role)
    {
        if (!in_array($role, $this->$permissionProperty)) {
            array_push($this->$permissionProperty, $role);

            // Once custom permissions are set, we need to remove the "all" setting
            $this->forbidPermissionForRole($permissionProperty, self::AUTHORIZATION_ALLOW_ALL);
        }

        return $this;
    }

    /**
     * Forbid the permission for the specified role.
     *
     * @param string $permissionProperty
     * @param mixed $role
     * @return FieldAbstract
     */
    private function forbidPermissionForRole($permissionProperty, $role)
    {
        if (in_array($role, $this->$permissionProperty)) {
            $this->$permissionProperty = array_diff($this->$permissionProperty, array($role));

            // Once custom permissions are set, we need to remove the "all" setting
            if (self::AUTHORIZATION_ALLOW_ALL !== $role) {
                $this->forbidPermissionForRole($permissionProperty, self::AUTHORIZATION_ALLOW_ALL);
            }
        }

        return $this;
    }

    /**
     * Check to see if the UserInterface can use the specific capability.
     * We return true if the capability is available to everyone or if the user
     * has a role allowed for that permission/capability.
     *
     * @param string $permissionProperty
     * @param UserInterface $user
     * @return boolean
     */
    private function checkPermissionForUser($permissionProperty, UserInterface $user = null)
    {
        if (in_array(self::AUTHORIZATION_ALLOW_ALL, $this->$permissionProperty)) {
            return true;
        }

        foreach ($this->$permissionProperty as $role) {
            if ($user && $user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the permission.  If true, it's allowed for everyone.  If false, it's
     * forbidden for everyone.  Otherwise, we expect an array of roles for which
     * the capability is allowed.
     *
     * @param string $permissionProperty
     * @param mixed $value
     * @throws Exception
     * @return FieldAbstract
     */
    private function setPermission($permissionProperty, $value)
    {
        if (true === $value) {
            $this->$permissionProperty = array(self::AUTHORIZATION_ALLOW_ALL);
        } elseif (false === $value) {
            $this->$permissionProperty = array();
        } elseif (is_array($value)) {
            $this->$permissionProperty = $value;
        } else {
            throw new Exception('Permission value must be boolean or array of roles');
        }

        return $this;
    }
}
