<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Fields\Helper\SelectSort;
use Dewdrop\Request;

class Listing
{
    private $db;

    private $request;

    private $select;

    private $fields;

    private $prefix = '';

    private $visibilityFilter;

    public function __construct(DbAdapter $db, Select $select, Fields $fields, Request $request = null)
    {
        $this->db      = $db;
        $this->select  = $select;
        $this->fields  = $fields;
        $this->request = ($request ?: new Request());

        $this->selectSortHelper = new SelectSort($db);
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getSelectSortHelper()
    {
        return $this->selectSortHelper;
    }

    public function fetchData()
    {
        $select = $this->selectSortHelper->sortByRequest(
            $this->fields->getSortableFields(),
            $this->select,
            $this->request,
            $this->prefix
        );

        return $this->db->fetchAll($select);
    }

    public function __call($method, array $args)
    {
        return call_user_func_array(array($this->fields, $method), $args);
    }
}
