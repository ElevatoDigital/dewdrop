<?php

namespace Dewdrop\Auth\Db;

use Dewdrop\Auth\UserInterface as AuthUserInterface;
use Dewdrop\Fields\UserInterface as FieldsUserInterface;
use Dewdrop\Db\Row;
use Dewdrop\Pimple;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface as SfSecurityUserInterface;

/**
 * User database row class
 */
class UserRowGateway extends Row implements AuthUserInterface, FieldsUserInterface, SfSecurityUserInterface
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
        // No-op in Dewdrop core.  Implement at application-level if needed.
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
        return $this->get('password_hash');
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
        return array($this->get('role'));
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
        return $this->get('password_hash');
    }

    public function getId()
    {
        return $this->get('user_id');
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->get('username');
    }

    public function getEmailAddress()
    {
        return $this->get('email_address');
    }

    public function getShortName()
    {
        return $this->get('first_name') . ' ' . substr($this->get('last_name'), 0, 1);
    }

    public function getFullName()
    {
        return $this->get('first_name') . ' ' . $this->get('last_name');
    }

    public function hashPassword($plaintextPassword)
    {
        $encoder = Pimple::getResource('security.encoder.digest');

        $this->set(
            'password_hash',
            $encoder->encodePassword(trim($plaintextPassword), '')
        );

        return $this;
    }

    public function __sleep()
    {
        return array('columns', 'data');
    }

    public function __wakeup()
    {
        $this->setTable(new UsersTableGateway());
    }
}
