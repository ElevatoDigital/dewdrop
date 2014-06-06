<?php

namespace Dewdrop\Fields;

use Dewdrop\Fields as FieldsSet;

abstract class FieldAbstract implements FieldInterface
{
    const AUTHORIZATION_ALLOW_ALL = 'authorization:allow:all';

    protected $fieldsSet;

    protected $visible = array();

    protected $sortable = array();

    protected $filterable = array();

    protected $editable = array();

    protected $customHelperCallbacks = array();

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
    }

    public function setFieldsSet(FieldsSet $fieldsSet)
    {
        $this->fieldsSet = $fieldsSet;

        return $this;
    }

    public function __call($method, array $args)
    {
        if (!$this->fieldsSet) {
            throw new Exception("{$method} not found on field and not Fields object assigned");
        }

        return call_user_func_array(array($this->fieldsSet, $method), $args);
    }

    public function setVisible($visible)
    {
        return $this->setPermission('visible', $visible);
    }

    public function isVisible(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('visible', $user);
    }

    public function allowVisbilityForRole($role)
    {
        return $this->allowPermissionForRole('visible', $role);
    }

    public function forbidVisibilityForRole($role)
    {
        return $this->forbidPermissionForRole('visible', $role);
    }

    public function setSortable($sortable)
    {
        return $this->setPermission('sortable', $sortable);
    }

    public function isSortable(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('sortable', $user);
    }

    public function allowSortingForRole($role)
    {
        return $this->allowPermissionForRole('sortable', $role);
    }

    public function forbidSortingForRole($role)
    {
        return $this->forbidPermissionForRole('sortable', $role);
    }

    public function setFilterable($filterable)
    {
        return $this->setPermission('filterable', $filterable);
    }

    public function isFilterable(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('filterable', $user);
    }

    public function allowFilteringForRole($role)
    {
        return $this->allowPermissionForRole('filterable', $role);
    }

    public function forbidFilteringForRole($role)
    {
        return $this->forbidPermissionForRole('filterable', $role);
    }

    public function setEditable($editable)
    {
        return $this->setPermission('editable', $editable);
    }

    public function isEditable(UserInterface $user = null)
    {
        return $this->checkPermissionForUser('editable', $user);
    }

    public function allowEditingForRole($role)
    {
        return $this->allowPermissionForRole('editable', $role);
    }

    public function forbidEditingForRole($role)
    {
        return $this->forbidPermissionForRole('editable', $role);
    }

    public function assignHelperCallback($helperName, $callable)
    {
        $this->customHelperCallbacks[strtolower($helperName)] = $callable;

        return $this;
    }

    public function hasHelperCallback($helperName)
    {
        return array_key_exists(strtolower($helperName), $this->customHelperCallbacks);
    }

    public function getHelperCallback($helperName)
    {
        return $this->customHelperCallbacks[strtolower($helperName)];
    }

    private function allowPermissionForRole($permissionProperty, $role)
    {
        if (!in_array($role, $this->$permissionProperty)) {
            array_push($this->$permissionProperty, $role);
        }

        return $this;
    }

    private function forbidPermissionForRole($permissionProperty, $role)
    {
        if (in_array($role, $this->$permissionProperty)) {
            $this->$permissionProperty = array_diff($this->$permisionsProperty, array($role));
        }

        return $this;
    }

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
