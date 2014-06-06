<?php

namespace Dewdrop\Fields\Listing;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\Listing;

class Column
{
    private $id;

    private $label;

    private $sortable = false;

    private $visible = true;

    public function __construct(Listing $listing, array $options = array())
    {
        $this->listing = $listing;

        $this->setOptions($options);
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->listing, $method), $args);
    }

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

    public function setDbField(DbField $dbField)
    {
        $this->dbField = $dbField;

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
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

    public function setSortable($sortable)
    {
        $this->sortable = (boolean) $sortable;

        return $this;
    }

    public function setVisible($visible)
    {
        $this->visible = (boolean) $visible;

        return $this;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function isVisible()
    {
        return $this->visible;
    }
}
