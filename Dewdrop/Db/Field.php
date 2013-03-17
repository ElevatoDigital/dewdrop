<?php

namespace Dewdrop\Db;

/**
 * @package Dewdrop
 */
class Field
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $note = '';

    /**
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * @var \Dewdrop\Db\Row
     */
    private $row;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $metadata;

    public function __construct(Table $table, $name, array $metadata)
    {
        $this->table    = $table;
        $this->name     = $name;
        $this->metadata = $metadata;
    }

    public function setRow(Row $row)
    {
        $this->row = $row;

        return $this;
    }

    public function setValue($value)
    {
        $this->row->set($this->name, $value);

        return $this;
    }

    public function getValue()
    {
        return $this->row->get($this->name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $label
     * @return \Dewdrop\Db\Field
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = $this->inflectLabel();
        }

        return $this->label;
    }

    /**
     * @param string $note
     * @return \Dewdrop\Db\Field
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    public function getControlName()
    {
        return $this->table->getTableName() . ':' . $this->name;
    }

    /**
     * Generate a label for this field based up the underlying database
     * column's name.
     *
     * @return string
     */
    private function inflectLabel()
    {
        return ucwords(
            str_replace(
                array(' Of ', ' The ', ' A '),
                array(' of ', ' the ', ' a '),
                preg_replace('/_id$/', '', $this->name)
            )
        );
    }
}
