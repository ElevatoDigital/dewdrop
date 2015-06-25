<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy\Exception;

use Dewdrop\Db\Dbdeploy\Exception as BaseException;

/**
 * This exception is thrown when a SQL file in a changeset's path
 * does not match the expected xxxxx-short-description.sql style.
 */
class InvalidFilename extends BaseException
{

}
