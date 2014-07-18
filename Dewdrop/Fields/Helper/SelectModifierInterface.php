<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Db\Select;
use Dewdrop\Fields;

/**
 * This interface is for all field helpers that modify Select objects
 * (e.g. sorting, filtering, etc.).
 */
interface SelectModifierInterface
{
    public function setPrefix($prefix);

    public function getPrefix();

    /**
     * Check to see if the SelectModifier matches the supplied name.
     *
     * @param string $name
     * @return boolean
     */
    public function matchesName($name);

    /**
     * Using the supplied \Dewdrop\Fields and \Dewdrop\Db\Select, modify the
     * Select and return it.
     *
     * @param Fields $fields
     * @param Select $select
     * @return Select
     */
    public function modifySelect(Fields $fields, Select $select);
}
