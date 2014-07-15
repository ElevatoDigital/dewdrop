<?php

namespace Dewdrop\Auth\Db;

use Dewdrop\Db\Table;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Users database table class
 */
class UsersTableGateway extends Table implements UserProviderInterface
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
            ->setRowClass('\Dewdrop\Auth\Db\UserRowGateway');
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        if (!$username) {
            throw new UsernameNotFoundException('Please provide a username.');
        } else {
            $user = $this->fetchRow(
                'SELECT * FROM users WHERE LOWER(username) = ?',
                array(trim(strtolower($username)))
            );

            if (!$user) {
                throw new UsernameNotFoundException('A user could not be found matching that username and password.');
            }

            return $user;
        }
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        $className = get_class($user);

        if (!$this->supportsClass($className)) {
            throw new UnsupportedUserException("{$className} is not a supported user class.");
        }

        return $this->loadUserByUsername($user->get('username'));
    }

    /**
     * @return \Dewdrop\Db\Select
     */
    public function selectAdminListing()
    {
        return $this->select()->from(['u' => 'users']);
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Dewdrop\Auth\Db\UserRowGateway';
    }
}
