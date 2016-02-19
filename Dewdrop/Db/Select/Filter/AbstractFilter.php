<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Select\Filter;

use Dewdrop\Db\Expr;
use Dewdrop\Db\Select;

/**
 * Abstract filter class
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * The name of the DB column you need to filter.
     *
     * @var string
     */
    protected $columnName;

    /**
     * The name of the table in which the filtered column is present.
     *
     * @var string
     */
    protected $tableName;

    /**
     * @var Expr
     */
    protected $expr;

    /**
     * This filter implementation can be instantiated in one of two ways:
     *
     * 1) By passing the table and column names, respectively
     * 2) By passing an arbitrary SQL expression as an instance of \Dewdrop\Db\Expr
     *
     * @throws Exception
     */
    public function __construct()
    {
        $args      = func_get_args();
        $argsCount = count($args);

        switch ($argsCount) {
            case 1: // Arbitrary SQL expression
                if (!$args[0] instanceof Expr) {
                    throw new Exception(
                        'You must either pass the table and column names, respectively, or pass an instance of ' .
                            '\Dewdrop\Db\Expr'
                    );
                }
                $this->expr = $args[0];
                break;
            case 2: // Table and column names, respectively
                $this->tableName  = (string) $args[0];
                $this->columnName = (string) $args[1];
                break;
            default:
                throw new Exception("Invalid number of arguments: {$argsCount}");
        }
    }

    protected function getComparisonExpression(Select $select)
    {
        if ($this->isExpr()) {
            return (string) $this->expr;
        } else {
            return $select->quoteWithAlias($this->tableName, $this->columnName);
        }
    }

    /**
     * @return bool
     */
    protected function isExpr()
    {
        return $this->expr instanceof Expr;
    }
}
