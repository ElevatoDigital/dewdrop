<?php

namespace Dewdrop\Db\Row;

use Dewdrop\Fields\UserInterface as DewdropFieldsUserInterface;
use Dewdrop\Db\Row;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface as SymfonySecurityUserInterface;

/**
 * User database row class
 */
class User extends Row implements DewdropFieldsUserInterface, SymfonySecurityUserInterface
{
    /**
     * Check to see if the user has the specified role.
     *
     * @param mixed $role
     * @return boolean
     */
    public function hasRole($role)
    {
        // @todo Implement hasRole() method.
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // @todo Implement eraseCredentials() method.
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        // @todo Implement getPassword() method.
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        // @todo Implement getRoles() method.
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // @todo Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        // @todo Implement getUsername() method.
    }
}