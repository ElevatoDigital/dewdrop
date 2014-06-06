<?php

namespace Dewdrop\Fields;

interface FieldInterface
{
    public function setLabel($label);

    public function getLabel();

    public function setId($id);

    public function getId();

    public function getHtmlId();

    public function assignHelperCallback($helperName, $callable);

    public function hasHelperCallback($helperName);

    public function getHelperCallback($helperName);

    public function setVisible($visible);

    public function isVisible(UserInterface $user = null);

    public function allowVisbilityForRole($role);

    public function forbidVisibilityForRole($role);

    public function setSortable($sortable);

    public function isSortable(UserInterface $user = null);

    public function allowSortingForRole($role);

    public function forbidSortingForRole($role);

    public function setFilterable($filterable);

    public function isFilterable(UserInterface $user = null);

    public function allowFilteringForRole($role);

    public function forbidFilteringForRole($role);

    public function setEditable($editable);

    public function isEditable(UserInterface $user = null);

    public function allowEditingForRole($role);

    public function forbidEditingForRole($role);
}
