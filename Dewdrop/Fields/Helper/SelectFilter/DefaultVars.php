<?php

namespace Dewdrop\Fields\Helper\SelectFilter;

use Dewdrop\Db\Field as DbField;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\HelperAbstract;

class DefaultVars extends HelperAbstract
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name = 'selectfilter.defaultvars';

    public function getDefaultVars(FieldInterface $field)
    {
        return call_user_func($this->getFieldAssignment($field));
    }

    public function detectCallableForField(FieldInterface $field)
    {
        return function () {
            return array();
        };
    }
}
