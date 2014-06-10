<?php

namespace Dewdrop\Fields\Filter;

use Dewdrop\Fields;

interface FilterInterface
{
    public function apply(Fields $fields);
}
