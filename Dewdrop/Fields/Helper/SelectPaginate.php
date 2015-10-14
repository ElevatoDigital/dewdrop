<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Select;
use Dewdrop\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields;
use Dewdrop\Request;

/**
 * This helper paginates a Select object so that a single page of a listing
 * can be retrieved at a time.  Yuo can adjust the number of records to
 * return per page.
 */
class SelectPaginate extends HelperAbstract implements SelectModifierInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'selectpaginate';

    /**
     * Whether modifications from this modifier should be applied at all.
     *
     * @var bool
     */
    private $enabled = true;

    /**
     * The current page.
     *
     * @var int
     */
    private $page;

    /**
     * The number of records to show per page.
     *
     * @var int
     */
    private $pageSize = 50;

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
     * Provide the HTTP request object that can be used to determine which page
     * is selected.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
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

    /**
     * There are no field-specific callables for pagination, so attempting to
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
     * Get current page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set number of records displayed per page.
     *
     * @param int $pageSize
     * @return SelectPaginate
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = (int) $pageSize;

        return $this;
    }

    /**
     * Get number of records displayed per page.
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
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
        if ($this->request->getQuery($this->prefix . 'disable-pagination')) {
            $this->disable();
        }

        $driver = $select->getAdapter()->getDriver();

        $this->page = (int) $this->request->getQuery($this->prefix . 'listing-page', 1);

        $driver->prepareSelectForTotalRowCalculation($select);

        if (!$this->enabled) {
            return $select;
        }

        return $select->limit(
            $this->getPageSize(),
            $this->getPageSize() * ($this->page - 1)
        );
    }
}
