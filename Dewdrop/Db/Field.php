<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db;

/**
 * Field objects provide a way to leverage database metadata throughout
 * your application and establish a centralized source of information about
 * how the field should be labeled, any notes that should be displayed with
 * it, any validators that should be included, etc.
 */
class Field
{
    /**
     * How this field should be labeled when included in UI such as form fields
     * or table headers.
     *
     * @var string
     */
    private $label;

    /**
     * Any notes that should be displayed along with this field when it is
     * displayed to users.
     *
     * @var string
     */
    private $note = '';

    /**
     * The table this field is associated with.
     *
     * @var \Dewdrop\Db\Table
     */
    private $table;

    /**
     * The row this field is associated with.  There will not always be a row
     * associated with the field.  The getValue() and setValue() methods will
     * not be functional unless a row is present.
     *
     * @var \Dewdrop\Db\Row
     */
    private $row;

    /**
     * The name of the column this field represents.
     *
     * @var string
     */
    private $name;

    /**
     * The metadata related to this column.  The metadata includes the
     * following fields:
     *
     * - SCHEMA_NAME
     * - TABLE_NAME
     * - COLUMN_NAME
     * - COLUMN_POSITION
     * - DATA_TYPE
     * - DEFAULT
     * - NULLABLE
     * - LENGTH
     * - SCALE
     * - PRECISION
     * - UNSIGNED
     * - PRIMARY
     * - PRIMARY_POSITION
     * - IDENTITY
     *
     * @var array
     */
    private $metadata;

    /**
     * An identifier for this field when it is associated with input controls
     * or other UI elements.  This can be changed manually to disambiguate
     * fields when multiple instances of a model and its fields are used on a
     * single request.
     *
     * By default, this property with have the value of:
     *
     * <code>
     * table_name:column_name
     * </code>
     *
     * @var string
     */
    private $controlName;

    /**
     * Create new field with a reference to the table that instantiated it,
     * the name of the DB column it represents and metadata from the DB about
     * its type, constraints, etc.
     *
     * @param Table $table
     * @param string $name
     * @param array $metadata
     */
    public function __construct(Table $table, $name, array $metadata)
    {
        $this->table    = $table;
        $this->name     = $name;
        $this->metadata = $metadata;
    }

    /**
     * Check whether the field is of the specified type.
     *
     * @param string $type
     * @return boolean
     */
    public function isType($type)
    {
        return $this->metadata['DATA_TYPE'] === $type;
    }

    /**
     * Associate a row with this field object so that it can be used to retrieve
     * and/or set the value of the associated column in the row.
     *
     * @param Row $row
     * @return \Dewdrop\Db\Field
     */
    public function setRow(Row $row)
    {
        $this->row = $row;

        return $this;
    }

    /**
     * Set the value of this field on the associated row, if available.
     *
     * @param mixed $value
     * @return \Dewdrop\Db\Field
     */
    public function setValue($value)
    {
        $this->row->set($this->name, $value);

        return $this;
    }

    /**
     * Retrieve the value of this field for the associated row, if available.
     * @return mixed
     */
    public function getValue()
    {
        return $this->row->get($this->name);
    }

    /**
     * Get the name of the DB column associated with this field.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Manually specify a label for this field, overriding the default
     * inflection-based naming.
     *
     * @param string $label
     * @return \Dewdrop\Db\Field
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label for this field, inflecting it from the DB column name if
     * one hasn't be assigned explicitly.
     *
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
     * Set a note that will be displayed alongside this field when it is used
     * in a UI.
     *
     * @param string $note
     * @return \Dewdrop\Db\Field
     */
    public function setNote($note)
    {
        $this->note = $note;
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
     * Manually override the default control name for this field.
     *
     * This can be useful and necessary if you are using multiple instances of
     * the same model and field on a single page and you need to disambiguate
     * them.
     *
     * @param string $controlName
     * @return \Dewdrop\Db\Field
     */
    public function setControlName($controlName)
    {
        $this->controlName = $controlName;

        return $this;
    }

    /**
     * Get the control name, using the default of "table_name:column_name" if
     * no control name has been set explicitly.
     *
     * @return string
     */
    public function getControlName()
    {
        if (null === $this->controlName) {
            $this->controlName = $this->table->getTableName() . ':' . $this->name;
        }

        return $this->controlName;
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
