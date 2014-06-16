<?php

namespace Dewdrop\Fields;

use Dewdrop\Fields;
use Dewdrop\Fields\GroupedFields\Group;

class GroupedFields extends Fields
{
    private $groups = array();

    public function addGroup($id)
    {
        $this->groups[$id] = new Group($this);

        return $this->groups[$id];
    }

    public function hasGroup($id)
    {
        return array_key_exists($id, $this->groups);
    }

    public function getGroup($id)
    {
        return $this->groups[$id];
    }

    public function getGroups()
    {
        return array_values($this->groups);
    }
}
