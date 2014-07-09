<?php

namespace Dewdrop\Db\Table;

use Dewdrop\Db\Table;

/**
 * Users database table class
 */
class Users extends Table
{
    /**
     * This method should be used by sub-classes to set the table name,
     * create field customization callbacks, etc.
     *
     * @return void
     */
    public function init()
    {
        $this
            ->setTableName('users')
            ->setRowClass('\Dewdrop\Db\Row\User');
    }
}