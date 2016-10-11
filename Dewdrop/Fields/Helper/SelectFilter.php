<?php

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Select;
use Dewdrop\Fields;
use Dewdrop\Fields\Helper\SelectFilter\DefaultVars;
use Dewdrop\Fields\Helper\SelectFilter\FilterType;
use Dewdrop\Fields\Helper\SelectFilter\SelectModifier;
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
        $this->defaultVarsHelper = new DefaultVars($request);
        $this->selectModifier    = new SelectModifier($request, $this->defaultVarsHelper);
    }

    public static function isQueryStringParamNotRelatedToFiltering($paramName, $paramPrefix)
    {
        return 0 !== strpos($paramName, $paramPrefix . 'ftr-') && $paramPrefix . 'listing-page' !== $paramName;
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

    public function addCustomFilter($filter, array $vars)
    {
        $this->selectModifier->addCustomFilter($filter, $vars);

        return $this;
    }

    public function hasFilters(Fields $fields = null)
    {
        if (null === $fields) {
            return $this->hasFiltersWithoutFields();
        } else {
            return 0 < count($this->selectModifier->getCurrentFilters($fields));
        }
    }

    /**
     * This is a fallback to an old hacky version of hasFilters() that didn't require the $fields param.  If
     * you call hasFilters() with no value for the $fields param, you'll end up here.  This won't catch cases where
     * a filter was added via DefaultVars.
     *
     * @deprecated
     * @return bool
     */
    private function hasFiltersWithoutFields()
    {
        if (0 < count($this->selectModifier->getCustomFilters())) {
            return true;
        }

        $queryKeys = array_keys($this->request->getQuery());

        foreach ($queryKeys as $key) {
            if (0 === stripos($key, $this->prefix . 'ftr-id_')) {
                return true;
            }
        }

        return false;
    }
}
