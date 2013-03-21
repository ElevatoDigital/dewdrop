<?php

namespace DewdropTest;

use Dewdrop\Db\Table;

class NoTableName extends Table
{
    public function init()
    {
        // Not setting table name in constructor to test that exception is thrown
    }
}
