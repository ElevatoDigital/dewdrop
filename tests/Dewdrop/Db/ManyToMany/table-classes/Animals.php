<?php

namespace DewdropTest\Db\ManyToMany;

use Dewdrop\Db\Table;

class Animals extends Table
{
    public function init()
    {
        $this
            ->setTableName('dewdrop_test_animals')
            ->hasMany('fruits', 'dewdrop_test_fruits_eaten_by_animals');
    }
}
