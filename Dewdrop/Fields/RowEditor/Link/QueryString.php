<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\RowEditor\Link;

use Dewdrop\Db\Table;
use Dewdrop\Request;

/**
 * Create a row object to attach to all the fields from the supplied
 * \Dewdrop\Db\Table using the value of a query string variable.  This linker
 * is often used for the primary row on an edit page, where the row's primary
 * key value is passed from a listing to the edit page in the query string.
 */
class QueryString implements LinkInterface
{
    /**
     * Provide the \Dewdrop\Request object and query string variable name that
     * will be used to get or create the row object.
     *
     * @param Request $request
     * @param string $variableName
     */
    public function __construct(Request $request, $variableName)
    {
        $this->request      = $request;
        $this->variableName = $variableName;
    }

    /**
     * Provide a row that can be linked to all the fields from the supplied
     * table.  If the query string variable has a value, we'll attempt to get
     * the row from the table's find() method.  Otherwise, we'll create a new
     * row.
     *
     * @param Table $table
     * @return \Dewdrop\Db\Row
     */
    public function link(Table $table)
    {
        $value = $this->request->getQuery($this->variableName);

        if ($value) {
            $row = $table->find($value);
        } else {
            $row = $table->createRow();
        }

        return $row;
    }
}
