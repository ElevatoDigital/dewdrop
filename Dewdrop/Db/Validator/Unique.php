<?php

namespace Dewdrop\Db\Validator;

use Dewdrop\Db\Field;
use Dewdrop\Db\UniqueConstraint;
use Zend\Validator\AbstractValidator;

class Unique extends AbstractValidator
{
    const NOT_UNIQUE = 'notUnique';

    protected $messageTemplates = [
        self::NOT_UNIQUE => 'This %label% is already in use.'
    ];

    protected $messageVariables = [
        'label' => 'label'
    ];

    /**
     * @var Field
     */
    protected $field;

    /*
     * @var UniqueConstraint
     */
    protected $uniqueConstraint;

    /**
     * @var string
     */
    protected $label;

    public function __construct(Field $field, UniqueConstraint $uniqueConstraint)
    {
        parent::__construct();

        $this->field            = $field;
        $this->label            = $field->getLabel();
        $this->uniqueConstraint = $uniqueConstraint;
    }

    public function isValid($value)
    {
        if ($this->uniqueConstraint->fieldValueIsUnique($this->field, $value)) {
            return true;
        } else {
            $this->error(self::NOT_UNIQUE);
            return false;
        }
    }
}