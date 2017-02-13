<?php

namespace Model;

use Dewdrop\Db\Table;

class SecurityLevels extends Table
{
    const ADMIN = 1;

    public function init()
    {
        $this->setTableName('security_levels');
    }
}
