<?php

namespace DewdropTest;

use Dewdrop\Fields\UserInterface;

class TestUser implements UserInterface
{
    public function hasRole($role)
    {
        return true;
    }
}
