<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Field;
use Dewdrop\Db\Select;
use Dewdrop\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields;
use Dewdrop\Request;

/**
 * This helper allows for a listing to be filtered to either show or hide deleted records.
 */
class SelectDeletedRecords extends HelperAbstract implements SelectModifierInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'selectdeletedrecords';

    /**
     * Whether modifications from this modifier should be applied at all.
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * @var Field
     */
    private $field;

    /**
     * A Request object we can use to look up the current page.
     *
     * @var \Dewdrop\Request
     */
    private $request;

    /**
     * A param prefix that can be used if you have multiple paginated listings
     * displayed on a single page.
     *
     * @var string
     */
    private $prefix;

    /**
     * @var bool
     */
    private $showingDeletedRecords = false;

    /**
     * Provide the HTTP request object that can be used to determine which page
     * is selected.
     *
     * @param Request $request
     */
    public function __construct(Request $request, Field $field)
    {
        $this->field   = $field;
        $this->request = $request;
    }

    /**
     * Set a prefix that can be used on HTTP parameters to avoid collisions
     * with other paginated listings.
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function isShowingDeletedRecords()
    {
        return $this->showingDeletedRecords;
    }

    /**
     * Check to see if this helper is enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Enable this modifier.
     *
     * @return $this
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Disable this modifier.
     *
     * @return $this
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Get the HTTP param prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getQueryParameterName()
    {
        return $this->prefix . 'show-deleted';
    }

    /**
     * There are no field-specific callables for deleted records, so attempting to
     * look them up always returns false.
     *
     * @param FieldInterface $field
     * @return false
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return false;
    }

    /**
     * Using the supplied \Dewdrop\Fields and \Dewdrop\Db\Select, modify the
     * Select to include only the current page with the correct number of
     * records.  The DB driver is used to ensure we can get the total number
     * of records that _would_ have been returned had no pagination been applied
     * after the query has been executed (using whatever facility is provided
     * for that use in the specific RDBMS).
     *
     * @param Fields $fields
     * @param Select $select
     * @return Select
     * @throws Exception
     */
    public function modifySelect(Fields $fields, Select $select)
    {
        if (!$this->isEnabled()) {
            return $select;
        }

        $column = $select->quoteWithAlias(
            $this->field->getTable()->getTableName(),
            $this->field->getName()
        );

        $this->showingDeletedRecords = (boolean) $this->request->getQuery($this->getQueryParameterName());

        if ($this->isShowingDeletedRecords()) {
            return $select->where("{$column} = true");
        } else {
            return $select->where("{$column} = false");
        }
    }
}
