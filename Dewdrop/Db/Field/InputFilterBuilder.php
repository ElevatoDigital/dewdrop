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
use Dewdrop\Filter\NullableDbBoolean as NullableDbBooleanFilter;
use Zend\Filter;
use Zend\Filter\FilterChain;
use Zend\InputFilter\Input;
use Zend\Validator;
use Zend\Validator\ValidatorChain;

/**
 * This class adds default filters and validators to a field's input filter,
 * depending upon its type.
 */
class InputFilterBuilder
{
    /**
     * Examine the field's type and attach filters and validators accordingly.
     *
     * @param Field $field
     * @param Input $input
     * @return Input
     */
    public function createInputForField(Field $field)
    {
        $input = new Input($field->getControlName());

        $validators = $input->getValidatorChain();
        $filters    = $input->getFilterChain();
        $metadata   = $field->getMetadata();

        if ($field->isRequired() && !$field->isType('boolean')) {
            $input->setAllowEmpty(false);
        } else {
            $input->setAllowEmpty(true);
        }

        if (!$field->isType('manytomany')) {
            return $input;
        }

        if ($field->isType('string')) {
            $this->attachForString($validators, $filters, $metadata['LENGTH']);
        } elseif ($field->isType('date')) {
            $this->attachForDate($validators, $filters);
        } elseif ($field->isType('boolean')) {
            $this->attachForBoolean($validators, $filters, $metadata['NULLABLE']);
        } elseif ($field->isType('integer')) {
            $this->attachForInteger($validators, $filters);
        } elseif ($field->isType('float')) {
            $this->attachForFloat($validators, $filters);
        }

        return $input;
    }

    /**
     * Attach validators and filters for strings.  If a length is specified in
     * the DB metadata, we use that to add a StringLength validator.  We always
     * add filters to trim strings and convert empty strings to null.
     *
     * @param ValidatorChain $validators
     * @param FilterChain $filters
     * @param null|int $length
     */
    protected function attachForString(ValidatorChain $validators, FilterChain $filters, $length)
    {
        if ($length && 0 < (int) $length) {
            $validators->attach(new Validator\StringLength(0, $length));
        }

        $filters->attach(new Filter\StringTrim());
        $filters->attach(new Filter\Null(Filter\Null::TYPE_STRING));
    }

    /**
     * Attach validator for date fields.
     *
     * @param ValidatorChain $validators
     * @param FilterChain $filters
     */
    protected function attachForDate(ValidatorChain $validators, FilterChain $filters)
    {
        $validators->attach(new Validator\Date());
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
     * @param ValidatorChain $validators
     * @param FilterChain $filters
     * @param boolean $nullable
     */
    protected function attachForBoolean(ValidatorChain $validators, FilterChain $filters, $nullable)
    {
        if ($nullable) {
            $filters->attach(new NullableDbBooleanFilter());
        } else {
            $filters->attach(new Filter\Int());
        }
    }

    /**
     * Ensure integer fields are filtered to ints.
     *
     * @param ValidatorChain $validators
     * @param FilterChain $filters
     */
    protected function attachForInteger(ValidatorChain $validators, FilterChain $filters)
    {
        $filters->attach(new Filter\Int());
        $validators->attach(new \Zend\I18n\Validator\Int());
    }

    /**
     * Ensure float fields are filtered to floats.
     *
     * @param ValidatorChain $validators
     * @param FilterChain $filters
     */
    protected function attachForFloat(ValidatorChain $validators, FilterChain $filters)
    {
        $filters->attach(new Filter\Digits());
        $validators->attach(new \Zend\I18n\Validator\Float());
    }
}
