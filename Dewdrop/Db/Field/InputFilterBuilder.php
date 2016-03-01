<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Field;

use Dewdrop\Db\Field;
use Dewdrop\Filter\IsoDate as IsoDateFilter;
use Dewdrop\Filter\IsoTimestamp as IsoTimestampFilter;
use Dewdrop\Filter\NullableDbBoolean as NullableDbBooleanFilter;
use Dewdrop\Filter\NullableDbInteger as NullableDbIntegerFilter;
use Dewdrop\Filter\NullableDbFloat as NullableDbFloatFilter;
use Zend\Filter;
use Zend\InputFilter\Input;
use Zend\Validator;

/**
 * This class adds default filters and validators to a field's input filter,
 * depending upon its type.
 */
class InputFilterBuilder
{
    /**
     * The types for which we'll be handling adding inputs/filters.
     *
     * @var array
     */
    protected $types = [
        'ManyToMany',
        'String',
        'Timestamp',
        'Date',
        'Boolean',
        'Integer',
        'Float'
    ];

    /**
     * Metadata from the DB for the provided field.
     *
     * @var array
     */
    protected $metadata;

    /**
     * Examine the field's type and attach filters and validators accordingly.
     *
     * @param Field $field
     * @param array $metadata
     * @return Input
     */
    public function createInputForField(Field $field, array $metadata)
    {
        $this->metadata = $metadata;

        $input = $this->instantiateInput($field);

        foreach ($this->types as $type) {
            if ($field->isType($type)) {
                $method = "attachFor{$type}";
                $input  = $this->$method($input);
                break;
            }
        }

        return $input;
    }

    /**
     * Instantiate an Input object for the supplied field call setAllowEmpty()
     * depending upon whether the field is required.
     *
     * @param Field $field
     * @return Input
     */
    protected function instantiateInput(Field $field)
    {
        $input = new Input($field->getControlName());

        if ($field->isRequired() && !$field->isType('boolean')) {
            $input->setAllowEmpty(false);
        } else {
            $input->setAllowEmpty(true);
        }

        return $input;
    }

    /**
     * The only reason we provide a special case for ManyToMany, really, is to
     * avoid the normal integer filters/validators being applied to what will
     * not be a scalar value.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForManyToMany(Input $input)
    {
        return $input;
    }

    /**
     * Attach validators and filters for strings.  If a length is specified in
     * the DB metadata, we use that to add a StringLength validator.  We always
     * add filters to trim strings and convert empty strings to null.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForString(Input $input)
    {
        $length = $this->metadata['LENGTH'];

        if ($length && 0 < (int) $length) {
            $input->getValidatorChain()->attach(new Validator\StringLength(0, $length));
        }

        $input->getFilterChain()->attach(new Filter\StringTrim());
        $input->getFilterChain()->attach(new Filter\ToNull(Filter\ToNull::TYPE_STRING));

        return $input;
    }

    /**
     * Attach validator for timestamp fields.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForTimestamp(Input $input)
    {
        $input->getFilterChain()->attach(new IsoTimestampFilter());
        $input->getValidatorChain()->attach(new Validator\Date(['format' => 'Y-m-d H:i:s']));

        return $input;
    }

    /**
     * Attach validator for date fields.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForDate(Input $input)
    {
        $input->getFilterChain()->attach(new IsoDateFilter());
        $input->getValidatorChain()->attach(new Validator\Date());

        return $input;
    }

    /**
     * For booleans, we handle filtering differently depending upon whether the
     * column is nullable in the DB.  If it's nullable, we try to enable use of all
     * three possible states (null, false and true) by filtering empty strings or
     * null values to null and filtering other values ("0", 0, false, "1", etc.) to
     * an integer of zero or one that can be used nicely with the DB to communicate
     * the boolean value.  If the DB column is not nullable, we convert all values
     * to integers.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForBoolean(Input $input)
    {
        if ($this->metadata['NULLABLE']) {
            $input->getFilterChain()->attach(new NullableDbBooleanFilter());
        } else {
            $input->getFilterChain()->attach(new Filter\ToInt());
        }

        return $input;
    }

    /**
     * Ensure integer fields are filtered to ints.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForInteger(Input $input)
    {
        if ($this->metadata['NULLABLE']) {
            $input->getFilterChain()->attach(new NullableDbIntegerFilter());
        } else {
            $input->getFilterChain()
                ->attach(new Filter\ToNull(Filter\ToNull::TYPE_STRING))
                ->attach(new Filter\ToInt());
        }

        $input->getValidatorChain()->attach(new \Zend\I18n\Validator\IsInt());

        return $input;
    }

    /**
     * Ensure float fields are filtered to floats.
     *
     * @param Input $input
     * @return Input
     */
    protected function attachForFloat(Input $input)
    {
        if ($this->metadata['NULLABLE']) {
            $input->getFilterChain()->attach(new NullableDbFloatFilter());
        } else {
            $input->getFilterChain()->attach(new Filter\Callback(function ($value) {
                return preg_replace('/[^0-9.-]/', '', $value);
            }));
            $input->getValidatorChain()->attach(new \Zend\I18n\Validator\IsFloat());
        }

        return $input;
    }
}
