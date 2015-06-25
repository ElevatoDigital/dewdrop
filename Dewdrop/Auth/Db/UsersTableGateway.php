<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Auth\Db;

use Dewdrop\Db\Table;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Role\Role;
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
     * @return \Dewdrop\Auth\Db\UserRowGateway
     * @see UsernameNotFoundException
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        if (!$username) {
            throw new UsernameNotFoundException('Please provide a username.');
        } else {
            $rowData = $this->getAdapter()->fetchRow(
                'SELECT u.*
                FROM users u
                WHERE LOWER(u.username) = ?',
                [trim(strtolower($username))]
            );

            if (null === $rowData) {
                throw new UsernameNotFoundException('A user could not be found matching that username and password.');
            }

            /* @var $user \Dewdrop\Auth\Db\UserRowGateway */
            $user = $this->createRow($rowData);

            $user->setRole(new Role($rowData['security_level_id']));

            return $user;
        }
    }

    /**
     * Returns the user for the given email address or null on failure
     *
     * @param string $emailAddress
     * @return \Dewdrop\Auth\Db\UserRowGateway|null
     */
    public function loadUserByEmailAddress($emailAddress)
    {
        $user = null;

        $rowData = $this->getAdapter()->fetchRow(
            'SELECT u.*
            FROM users u
            WHERE LOWER(u.email_address) = ?',
            [trim(strtolower($emailAddress))]
        );

        if (null !== $rowData) {
            /* @var $user \Dewdrop\Auth\Db\UserRowGateway */
            $user = $this->createRow($rowData);
            $user->setRole(new Role($rowData['security_level_id']));
        }

        return $user;
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
     * Returns a select object for the admin listing
     *
     * @return \Dewdrop\Db\Select
     */
    public function selectAdminListing()
    {
        return $this->select()
            ->from(['u' => 'users'])
            ->join(
                ['sl' => 'security_levels'],
                'u.security_level_id = sl.security_level_id',
                ['security_level' => 'name']
            );
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
