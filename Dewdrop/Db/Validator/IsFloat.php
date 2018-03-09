<?php

namespace Dewdrop\Db\Validator;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\StringUtils;
use Zend\Validator\AbstractValidator;


class IsFloat extends AbstractValidator
{
    const INVALID   = 'floatInvalid';
    const NOT_FLOAT = 'notFloat';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::NOT_FLOAT => "The input does not appear to be a float",
    );

    /**
     * Constructor for the integer validator
     *
     * @param array|Traversable $options
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        parent::__construct($options);
    }

    /**
     * Returns true if and only if $value is a floating-point value.
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_scalar($value) || is_bool($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);

        if (is_float($value) || is_int($value)) {
            return true;
        }

        $valueFiltered = filter_var($value, FILTER_VALIDATE_FLOAT);

        if (!$valueFiltered) {
            $this->error(self::NOT_FLOAT);
            return false;
        }

        return true;
    }
}
