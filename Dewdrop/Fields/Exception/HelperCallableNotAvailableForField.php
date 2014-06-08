<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Exception;

use Dewdrop\Fields\Exception;

/**
 * A custom exception class that can be caught in cases where a field is
 * missing a callback (and no default could be provided) for a Fields API
 * helper.
 */
class HelperCallableNotAvailableForField extends Exception
{

}
