<?php

namespace DewdropTest;

use Dewdrop\Fields\UserInterface;

class TestUser implements UserInterface
{
    private $roles = array('admin');

    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }
}
