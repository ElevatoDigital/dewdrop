<?php

namespace Dewdrop\Fields\Filter;

use Dewdrop\Fields;

use Dewdrop\Db\Adapter as DbAdapter;

class Groups implements FilterInterface
{
    private $componentName;

    private $dbAdapter;

    private $dbTableName;

    public function __construct($componentName, DbAdapter $dbAdapter, $dbTableName = 'dewdrop_grouped_fields')
    {
        $this->componentName = $componentName;
        $this->dbAdapter     = $dbAdapter;
        $this->dbTableName   = $dbTableName;
    }

    public function load()
    {
    }

    public function apply(Fields $fields)
    {

    }
}
