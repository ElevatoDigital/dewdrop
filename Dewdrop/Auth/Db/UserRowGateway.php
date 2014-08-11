<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Auth\Db;

use Dewdrop\Auth\UserInterface as AuthUserInterface;
use Dewdrop\Fields\UserInterface as FieldsUserInterface;
use Dewdrop\Db\Row;
use Dewdrop\Pimple;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface as SfSecurityUserInterface;

/**
 * User database row data gateway class
 */
class UserRowGateway extends Row implements AuthUserInterface, FieldsUserInterface, SfSecurityUserInterface
{
    /**
     * Role
     *
     * @var Role
     */
    protected $role;

    /**
     * Check to see if the user has the specified role.
     *
     * @param Role|string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if (!$role instanceof Role) {
            $role = new Role($role);
        }

        return $this->role->getRole() == $role->getRole();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
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
     * <pre>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </pre>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return [$this->role];
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return $this->get('password_hash');
    }

    /**
     * Get ID
     *
     * @return int
     */
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

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->get('email_address');
    }

    /**
     * Get short name (e.g., Jane D)
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->get('first_name') . ' ' . substr($this->get('last_name'), 0, 1);
    }

    /**
     * Get full name (e.g., John Doe)
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->get('first_name') . ' ' . $this->get('last_name');
    }

    /**
     * Hashes the given plain text password and stores the result, which can be retrieved with getPassword()
     *
     * @param string $plaintextPassword
     * @return UserRowGateway
     */
    public function hashPassword($plaintextPassword)
    {
        $encoder = Pimple::getResource('security.encoder.digest');

        $this->set(
            'password_hash',
            $encoder->encodePassword(trim($plaintextPassword), '')
        );

        return $this;
    }

    /**
     * Set role
     *
     * @param Role $role
     * @return UserRowGateway
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Serialize columns and data only
     *
     * @return array
     */
    public function __sleep()
    {
        return array('columns', 'data');
    }

    /**
     * Set the table data gateway on unserialize()
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->setTable(new UsersTableGateway());
    }
}
