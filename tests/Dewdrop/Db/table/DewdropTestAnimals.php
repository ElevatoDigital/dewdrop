<?php

namespace DewdropTest;

use Dewdrop\Db\Table;

class DewdropTestAnimals extends Table
{
    public function init()
    {
        $this->setTableName('dewdrop_test_animals');
    }
}
