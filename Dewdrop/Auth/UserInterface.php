<?php

namespace Dewdrop\Auth;

interface UserInterface
{
    public function getId();

    public function getShortName();

    public function getFullName();

    public function getEmailAddress();

    public function getUsername();

    public function hashPassword($passwordHash);
}
