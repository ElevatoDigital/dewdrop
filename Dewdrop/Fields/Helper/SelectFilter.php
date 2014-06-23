<?php

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectFilter\DefaultVars;
use Dewdrop\Fields\Helper\SelectFilter\FilterType;
use Dewdrop\Fields\Helper\SelectFilter\SelectModifier;
use Dewdrop\Fields\Helper\SelectModifierInterface;
use Dewdrop\Request;

class SelectFilter implements SelectModifierInterface
{
    protected $name = 'selectfilter';

    private $request;

    private $prefix = '';

    private $filterTypeHelper;

    private $selectModifier;

    private $defaultVarsHelper;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->filterTypeHelper  = new FilterType();
        $this->selectModifier    = new SelectModifier($request);
        $this->defaultVarsHelper = new DefaultVars($request);
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

    public function matchesName($name)
    {
        return $this->name === strtolower($name);
    }

    public function getDefaultVarsHelper()
    {
        return $this->defaultVarsHelper;
    }

    public function getFilterTypeHelper()
    {
        return $this->filterTypeHelper;
    }

    public function getSelectModifier()
    {
        return $this->selectModifier;
    }

    public function modifySelect(Fields $fields, Select $select)
    {
        return $this->selectModifier->modifySelect($fields, $select);
    }

    public function hasFilters()
    {
        $queryKeys = array_keys($this->request->getQuery());

        foreach ($queryKeys as $key) {
            if (0 === stripos($key, $this->prefix . 'ftr-id_')) {
                return true;
            }
        }

        return false;
    }
}
