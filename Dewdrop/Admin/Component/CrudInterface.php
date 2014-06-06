<?php

namespace Dewdrop\Admin\Component;

interface CrudInterface
{
    public function getPrimaryModel();

    public function getListing();

    public function getFields();

    public function getVisibilityFilter();
}
