<?php

namespace DewdropTest\Model;

use Dewdrop\Db\Table;

class Animals extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_animals');
    }
}
