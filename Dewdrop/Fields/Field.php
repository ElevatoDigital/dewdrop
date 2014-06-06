<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Exception;
use Dewdrop\Fields as FieldsSet;

class Field extends FieldAbstract
{
    private $id;

    private $label;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getHtmlId()
    {
        return $this->getId();
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }
}
