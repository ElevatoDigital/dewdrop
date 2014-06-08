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
    /**
     * Using the supplied \Dewdrop\Fields and \Dewdrop\Db\Select, modify the
     * Select and return it.  The $paramPrefix may be used in order for your
     * helper to be able to reference prefixed request variables.  Prefixing
     * may be used in cases where multiple instances of the same component
     * are being rendered to the page and their GET or POST vars might
     * conflict otherwise.
     *
     * @param Fields $fields
     * @param Select $select
     * @param string $paramPrefix
     * @return Select
     */
    public function modifySelect(Fields $fields, Select $select, $paramPrefix = '');
}
