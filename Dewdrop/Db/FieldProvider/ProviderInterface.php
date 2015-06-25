<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\FieldProvider;

use Dewdrop\Db\Select;

/**
 * The field provider interface allows a table object to easily manage
 * fields coming from several different sources (i.e. physical DB columns,
 * many-to-many relationships, or EAV).  By checking for and creating
 * Field objects for this fields via the providers, the table object code
 * is cleaner and not riddled with a bunch of if/elseif/else logic
 * for the various field types.
 */
interface ProviderInterface
{
    /**
     * Check to see if a field exists with the supplied name.
     *
     * @param string $name
     * @return boolean
     */
    public function has($name);

    /**
     * Create a \Dewdrop\Db\Field object for the field with the given name.
     *
     * @param string $name
     * @return \Dewdrop\Db\Field
     */
    public function instantiate($name);

    /**
     * Get a list of field names available from this provider.
     *
     * @return array
     */
    public function getAllNames();

    /**
     * Augment the provided Select object with values from this field provider.
     *
     * @param Select $select
     * @return Select
     */
    public function augmentSelect(Select $select);
}
