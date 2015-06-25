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

/**
 * Table data gateway for user password change tokens
 */
class UserPasswordChangeTokensTableGateway extends Table
{
    /**
     * This method should be used by sub-classes to set the table name,
     * create field customization callbacks, etc.
     *
     * @return void
     */
    public function init()
    {
        $this->setTableName('user_password_change_tokens');
    }
}
