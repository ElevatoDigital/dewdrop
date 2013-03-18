<?php

namespace Dewdrop\Db;

/**
 * Expr allows you to inject raw SQL segments into a \Dewdrop\Db\Select or
 * other context in which Dewdrop might otherwise quote and therefore
 * misinterpret the code.
 *
 * @category   Dewdrop
 * @package    Db
 */
class Expr
{
    /**
     * Storage for the SQL expression.
     *
     * @var string
     */
    protected $expression;

    /**
     * Instantiate an expression, which is just a string stored as
     * an instance member variable.
     *
     * @param string $expression The string containing a SQL expression.
     */
    public function __construct($expression)
    {
        $this->expression = (string) $expression;
    }

    /**
     * @return string The string of the SQL expression stored in this object.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
