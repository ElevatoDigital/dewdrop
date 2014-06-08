<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields;

/**
 * This interface represents the methods that need to be present on the object
 * representing your user in order for that object to interact with the Fields
 * API.  It shouldn't necessarily contain methods for other user related methods,
 * like authentication.
 */
interface UserInterface
{
    /**
     * Check to see if the user has the specified role.
     *
     * @param mixed $role
     * @return boolean
     */
    public function hasRole($role);
}
