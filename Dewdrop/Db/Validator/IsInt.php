<?php

namespace Dewdrop\Db\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Validator\AbstractValidator;

class IsInt extends AbstractValidator
{
    const INVALID = 'intInvalid';
    const NOT_INT = 'notInt';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID => "Invalid type given. String or integer expected",
        self::NOT_INT => "The input does not appear to be an integer",
    );

    /**
     * Constructor for the integer validator
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        parent::__construct($options);
    }

    /**
     * Returns true if and only if $value is a valid integer
     *
     * @param  string|int $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        if (is_int($value)) {
            return true;
        }

        $this->setValue($value);

        $valueFiltered = filter_var($value, FILTER_VALIDATE_INT);

        if (!$valueFiltered) {
            $this->error(self::NOT_INT);
            return false;
        }

        return true;
    }
}
