<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Auth;

/**
 * User interface for auth components
 */
interface UserInterface
{
    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get short name (e.g., Jane D)
     *
     * @return string
     */
    public function getShortName();

    /**
     * Get full name (e.g., John Doe)
     *
     * @return string
     */
    public function getFullName();

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmailAddress();

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername();

    /**
     * Hashes the given plain text password and stores the result
     *
     * @param string $plaintextPassword
     * @return UserInterface
     */
    public function hashPassword($plaintextPassword);
}
