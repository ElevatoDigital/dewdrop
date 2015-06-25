<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db;

use Dewdrop\Db\Field\InputFilterBuilder;
use Dewdrop\Exception;
use Dewdrop\Fields\FieldAbstract;
use Dewdrop\Fields\OptionGroups;
use Dewdrop\Fields\OptionPairs;
use Dewdrop\Pimple;

/**
 * Field objects provide a way to leverage database metadata throughout
 * your application and establish a centralized source of information about
 * how the field should be labeled, any notes that should be displayed with
 * it, any validators that should be included, etc.
 */
class Field extends FieldAbstract
{
    /**
     * A \Dewdrop\Fields\OptionPairs object for use in retrieving key-value
     * pair options for a foreign key field.
     *
     * @var OptionPairs
     */
    protected $optionPairs;

    /**
     * A \Dewdrop\Fields\OptionGroups object for use in retrieving key-value
     * pair groups for a foreign key field.
     *
     * @var \Dewdrop\Fields\OptionGroups
     */
    protected $optionGroups;

    /**
     * The table this field is associated with.
     *
     * @var \Dewdrop\Db\Table
     */
    protected $table;

    /**
     * The row this field is associated with.  There will not always be a row
     * associated with the field.  The getValue() and setValue() methods will
     * not be functional unless a row is present.
     *
     * @var \Dewdrop\Db\Row
     */
    protected $row;

    /**
     * Whether this field is required or not
     *
     * @var boolean
     */
    protected $required;

    /**
     * The name of the column this field represents.
     *
     * @var string
     */
    protected $name;

    /**
     * How this field should be labeled when included in UI such as form fields
     * or table headers.
     *
     * @var string
     */
    private $label;

    /**
     * Typically, DB fields use IDs that are composed of the table name followed
     * by a separator and then the field name.  If you have a naming conflict,
     * though, you can set an alternate group name for the field.
     *
     * @var string
     */
    private $groupName;

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
     * <pre>
     * table_name:column_name
     * </pre>
     *
     * @var string
     */
    private $controlName;

    /**
     * An identifier suitable for use in an HTML ID attribute.
     *
     * @var string
     */
    private $htmlId;

    /**
     * The \Zend\InputFilter\Input instance used to validate and filter this
     * field.
     *
     * @var \Zend\InputFilter\Input
     */
    private $inputFilter;

    /**
     * Object used to populate this field's input filter based upon its type.
     *
     * @var InputFilterBuilder
     */
    private $inputFilterBuilder;

    /**
     * The ID for this field.  Used when interacting with the \Dewdrop\Fields
     * APIs.
     *
     * @var string
     */
    private $id;

    /**
     * All field capabilities are enabled by default for DB (whereas all are
     * disabled by default in custom fields).
     *
     * @var array
     */
    protected $visible = array(FieldAbstract::AUTHORIZATION_ALLOW_ALL);

    /**
     * All field capabilities are enabled by default for DB (whereas all are
     * disabled by default in custom fields).
     *
     * @var array
     */
    protected $sortable = array(FieldAbstract::AUTHORIZATION_ALLOW_ALL);

    /**
     * All field capabilities are enabled by default for DB (whereas all are
     * disabled by default in custom fields).
     *
     * @var array
     */
    protected $filterable = array(FieldAbstract::AUTHORIZATION_ALLOW_ALL);

    /**
     * All field capabilities are enabled by default for DB (whereas all are
     * disabled by default in custom fields).
     *
     * @var array
     */
    protected $editable = array(FieldAbstract::AUTHORIZATION_ALLOW_ALL);

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
     * Get a reference to the table that generated this field object.
     *
     * @return \Dewdrop\Db\Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Manually override the default setting regarding whether this field
     * is required
     *
     * @param boolean $required
     * @return \Dewdrop\Db\Field
     */
    public function setRequired($required)
    {
        $this->required = (boolean) $required;

        return $this;
    }

    /**
     * Check whether this field is required.  If setRequired() has not been
     * called, then we look to the DB metadata to determine whether the
     * field is required.  When the metadata says the column is not NULLABLE,
     * then it is marked as being required.
     *
     * @return boolean
     */
    public function isRequired()
    {
        if (null === $this->required) {
            $this->required = (false === $this->metadata['NULLABLE']);
        }

        return $this->required;
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
     * Check to see if this field has an associated row.
     *
     * @return boolean
     */
    public function hasRow()
    {
        return null !== $this->row;
    }

    /**
     * Get the row associated with this field.
     *
     * @return Row
     */
    public function getRow()
    {
        return $this->row;
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
     *
     * @throws Exception
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->row) {
            throw new Exception("Attempting to retrieve value for {$this->name} field with no row assigned.");
        }

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
     * Set the ID of this field.  If no ID is set on database fields, we'll
     * fall back to getControlName().
     *
     * @param string $id
     * @return \Dewdrop\Db\Field
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get this field's ID.  When no ID is set on a DB field, we fall back
     * to getControlName().
     *
     * @return string
     */
    public function getId()
    {
        return $this->id ?: $this->getControlName();
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

        if ($this->inputFilter) {
            $this->inputFilter->setName($this->controlName);
        }

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
            $this->controlName = $this->getGroupName() . ':' . $this->name;
        }

        return $this->controlName;
    }

    /**
     * Set an alternative group name for this field.  Typically, DB fields are
     * by their table name, but if you have fields from multiple instances of the
     * same model on a single request, that might cause conflicts.  In those cases,
     * altnative group names can be used to resolve the naming conflict.
     *
     * @param string $groupName
     * @return Field
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * Get the group name for this field, typically the table name, unless an
     * alternative has been set.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName ?: $this->table->getTableName();
    }

    /**
     * Override the default HTML ID for this field.
     *
     * @param string $htmlId
     * @return $this
     */
    public function setHtmlId($htmlId)
    {
        $this->htmlId = $htmlId;

        return $this;
    }

    /**
     * Get a version of the control name using underscores as word separators to
     * be more friendly in CSS selectors, etc.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->htmlId ?: str_replace(':', '_', $this->getControlName());
    }

    /**
     * Get a version of the control name using "+" for the model/field separator
     * to be more friendly to query strings.
     *
     * @return string
     */
    public function getQueryStringId()
    {
        return str_replace(':', '-', $this->getControlName());
    }

    /**
     * Get the \Zend\InputFilter\Filter object associated with this field.  This
     * object allows us to easily filter and validate values assigned to the
     * field.
     *
     * @return \Zend\InputFilter\Input
     */
    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->inputFilter = $this->getInputFilterBuilder()->createInputForField($this, $this->metadata);
        }

        return $this->inputFilter;
    }

    /**
     * Provide an alternative input filter build, if you'd like to use different
     * validators and filters for your field objects by default.
     *
     * @param InputFilterBuilder $inputFilterBuilder
     * @return $this
     */
    public function setInputFilterBuilder(InputFilterBuilder $inputFilterBuilder)
    {
        $this->inputFilterBuilder = $inputFilterBuilder;

        return $this;
    }

    /**
     * Get the InputFilterBuilder that can be used to create default validators
     * and filters for the field.
     *
     * @return InputFilterBuilder
     */
    public function getInputFilterBuilder()
    {
        return $this->inputFilterBuilder ?: Pimple::getResource('db.field.input-filter-builder');
    }

    /**
     * Get an OptionPairs object for this field.  Allows you to easily
     * fetch key-value option pairs for foreign keys.
     *
     * @return \Dewdrop\Fields\OptionPairs
     */
    public function getOptionPairs()
    {
        if (null === $this->optionPairs) {
            $this->optionPairs = new OptionPairs($this->table->getAdapter());

            $ref = $this->getOptionPairsReference();

            if ($ref) {
                $this->optionPairs->setOptions(
                    [
                        'tableName'   => $ref['table'],
                        'valueColumn' => $ref['column']
                    ]
                );
            }
        }

        return $this->optionPairs;
    }

    /**
     * Get an OptionGroups object for this field.  Allows you to easily
     * fetch key-value option pairs for foreign keys.
     *
     * @return \Dewdrop\Fields\OptionGroups
     */
    public function getOptionGroups()
    {
        if (null === $this->optionGroups) {
            $this->optionGroups = new OptionGroups($this->table->getAdapter());

            $ref = $this->getOptionPairsReference();

            if ($ref) {
                $this->optionGroups->setOptions(
                    [
                        'tableName'   => $ref['table'],
                        'valueColumn' => $ref['column'],
                        'optionPairs' => $this->getOptionPairs()
                    ]
                );
            }
        }

        return $this->optionGroups;
    }

    /**
     * Get the reference that can be used to retrieve option pairs.  How we retrieve
     * this will vary for one-to-many vs many-to-many contexts.
     *
     * @return array
     */
    protected function getOptionPairsReference()
    {
        return $this->table->getMetadata('references', $this->name);
    }

    /**
     * Convenience method that lets you access the validator chain directly
     * instead of first having to retrieve the input filter.
     *
     * @return \Zend\Validator\ValidatorChain
     */
    public function getValidatorChain()
    {
        return $this->getInputFilter()->getValidatorChain();
    }

    /**
     * Convenience method that lets you access the filter chain directly
     * instead of first having to retrieve the input filter.
     *
     * @return \Zend\Filter\FilterChain
     */
    public function getFilterChain()
    {
        return $this->getInputFilter()->getFilterChain();
    }

    /**
     * Generate a label for this field based up the underlying database
     * column's name.
     *
     * @return string
     */
    private function inflectLabel()
    {
        return str_replace(
            array(' Of ', ' The ', ' A ', ' From '),
            array(' of ', ' the ', ' a ', ' from '),
            ucwords(
                str_replace(
                    '_',
                    ' ',
                    preg_replace('/_id$/', '', $this->name)
                )
            )
        );
    }

    /**
     * Check whether the field is of the specified type.  One or more
     * types can be provided as individual arguments to this method.
     * If the field matches any of the supplied types, this method
     * will return true.
     *
     * @return boolean
     */
    public function isType()
    {
        $args = array_map('strtolower', func_get_args());

        if (in_array($this->metadata['GENERIC_TYPE'], $args) || in_array($this->metadata['DATA_TYPE'], $args)) {
            return true;
        }

        foreach ($args as $arg) {
            $method = 'isType' . ucfirst($arg);

            if (method_exists($this, $method) && $this->$method()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convenience method for checking for common string types.  You can call
     * isType('string') to check that your field matches one of the common
     * MySQL string types.
     *
     * @return boolean
     */
    protected function isTypeString()
    {
        return $this->isType('varchar', 'char', 'text');
    }

    /**
     * Convenience method to check if the field is a boolean.  You can call
     * isType('boolean') and isType() will in turn call this method to see
     * if the field is a boolean.
     *
     * @return boolean
     */
    protected function isTypeBoolean()
    {
        return $this->isType('tinyint');
    }

    /**
     * Check to see if this field is numeric, either integer or float.  Calling
     * isType('numeric') will delegate to this method automatically.
     *
     * @return boolean
     */
    protected function isTypeNumeric()
    {
        return $this->isTypeInteger() || $this->isTypeFloat();
    }

    /**
     * Check to see if this field matches any of the common MySQL integer types.
     * Calling isType('integer') will automatically delegate to this method.
     *
     * @return boolean
     */
    protected function isTypeInteger()
    {
        return 'integer' === $this->metadata['GENERIC_TYPE'];
    }

    /**
     * Check to see if this field matches any of the common MySQL float types.
     * Calling isType('float') will automatically delegate to this method.
     *
     * @return boolean
     */
    protected function isTypeFloat()
    {
        return 'float' === $this->metadata['GENERIC_TYPE'];
    }

    /**
     * Check to see if this field is a foreign key.  Calling isType('reference')
     * will automatically delegate to this method.
     *
     * @return boolean
     */
    protected function isTypeReference()
    {
        return (boolean) $this->table->getMetadata('references', $this->name);
    }

    /**
     * Check to see if this is a many-to-many field.  Always false here, always true
     * when Dewdrop\Db\ManyToMany\Field sub-classes.
     *
     * @return boolean
     */
    protected function isTypeManytomany()
    {
        return false;
    }
}
