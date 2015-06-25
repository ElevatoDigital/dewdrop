<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\RowEditor\Link;

use Dewdrop\Db\Table;

/**
 * One of the primary purposes of the \Dewdrop\Fields\RowEditor class is to
 * link rows objects to the database fields in a \Dewdrop\Fields collection.
 * Row objects are needed for editing because they provide a much nicer API
 * for saving and working with custom row-level business logic.
 *
 * When implementing a LinkInterface class, your constructor should receive
 * any dependencies needed for its look-up, which will be performed in
 * the link() method.
 */
interface LinkInterface
{
    /**
     * Return a row from the supplied table, using the linker's rules to
     * pull in any values needed for the look-up.
     *
     * @param Table $table
     * @return \Dewdrop\Db\Row
     */
    public function link(Table $table);
}
