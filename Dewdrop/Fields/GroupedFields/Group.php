<?php

namespace Dewdrop\Fields\GroupedFields;

use Dewdrop\Fields;
use Dewdrop\Fields\GroupedFields;

class Group extends Fields
{
    private $groupedFields;

    public function __construct(GroupedFields $groupedFields)
    {
        $this->groupedFields = $groupedFields;
    }

    public function add($field, $modelName = null)
    {
        $this->groupedFields->add($field, $modelName);

        return parent::add($field, $modelName);
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
