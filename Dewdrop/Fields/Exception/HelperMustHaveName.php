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
 * All helpers must have name properties defined so that fields can define
 * callbacks for them without having an instance of that helper at hand.
 * You should really only ever see this exception if you're developing a
 * new Fields API helper and have forgotten to fill out the name property.
 */
class HelperMustHaveName extends Exception
{

}
