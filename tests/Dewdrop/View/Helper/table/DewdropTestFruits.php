<?php

namespace DewdropViewHelperTest;

use Dewdrop\Db\Table;

class DewdropTestFruits extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_fruits');
    }
}
