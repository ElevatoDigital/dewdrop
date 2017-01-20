<?php

namespace Model;

use Dewdrop\Auth\Db\UsersTableGateway;

class Users extends UsersTableGateway
{
    public function init()
    {
        parent::init();

        $this
            ->setPluralTitle('User')
            ->setSingularTitle('Users');
    }

    /**
     * @inheritdoc
     */
    public function selectAdminListing()
    {
        return parent::selectAdminListing()
            ->where('NOT u.deleted');
    }
}
