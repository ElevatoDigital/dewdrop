<?php

namespace Dewdrop\Db\Adapter;

use Dewdrop\Db\Adapter as WpdbAdapter;

class Mock extends WpdbAdapter
{
    public function __construct()
    {
        $this->wpdb = new MockWpdb();
    }
}
