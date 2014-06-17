<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Driver\Pdo\Pgsql;
use Dewdrop\Db\Driver\Wpdb;
use Dewdrop\Db\Expr;
use Dewdrop\Db\Select;
use Dewdrop\Exception;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields;
use Dewdrop\Request;

/**
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
     * @var int
     */
    private $page;

    /**
     * Page size
     *
     * @var int
     */
    private $pageSize = 50;

    /**
     * A Request object we can use to look up the current page.
     *
     * @param Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param FieldInterface $field
     * @return false
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return false;
    }

    /**
     * Returns page
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets page size
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
     * Returns page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Using the supplied \Dewdrop\Fields and \Dewdrop\Db\Select, modify the
     * Select and return it.  The $paramPrefix may be used in order for your
     * helper to be able to reference prefixed request variables.  Prefixing
     * may be used in cases where multiple instances of the same component
     * are being rendered to the page and their GET or POST vars might
     * conflict otherwise.
     *
     * @param Fields $fields
     * @param Select $select
     * @param string $paramPrefix
     * @return Select
     * @throws Exception
     */
    public function modifySelect(Fields $fields, Select $select, $paramPrefix = '')
    {
        $driver = $select->getAdapter()->getDriver();

        $this->page = (int) $this->request->getQuery($paramPrefix . 'listing-page', 1);

        $driver->prepareSelectForTotalRowCalculation($select);

        return $select->limit(
            $this->getPageSize(),
            $this->getPageSize() * ($this->page - 1)
        );
    }
}
