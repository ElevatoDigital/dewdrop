<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\FieldInterface;

/**
 * All Fields API helpers must implement this interface.  There is a lot
 * of information about the helper API in HelperAbstract.
 *
 * @see \Dewdrop\Fields\Helper\HelperAbstract
 */
interface HelperInterface
{
    /**
     * Check to see if the supplied input matches this helper's name
     * (case-insensitive).
     *
     * @param string $name
     * @return boolean
     */
    public function matchesName($name);

    /**
     * Assign one more custom per-instance callbacks for this helper.  If the
     * $arguments param is an array, this method expects that the keys will be
     * field IDs and the values will be callables, assigning custom callbacks
     * for multiple fields in one call.  If, however, $assignments is a string
     * or a FieldInterface object, a single custom callback assignment will be
     * made.
     *
     * @param mixed $assignments
     * @param callable $callable
     * @return \Dewdrop\Fields\Helper\HelperAbstract
     */
    public function assign($assignments, callable $callable = null);

    /**
     * Get the callback that will be used for the given FieldInterface object.
     *
     * @param FieldInterface $field
     * @throws \Dewdrop\Fields\Exception\HelperCallableNotAvailableForField
     * @return callable
     */
    public function getFieldAssignment(FieldInterface $field);

    /**
     * Try to supply a default callback by looking at the supplied
     * FieldInterface object.  This method will only be called for a
     * field if no global or per-instance custom callbacks are assigned.
     * If no callback candidate is found, just return false from this method,
     * which will be detected by getFieldsAssigned(), causing execution to
     * halt.
     *
     * @param FieldInterface $field
     * @return mixed Either false or a callable.
     */
    public function detectCallableForField(FieldInterface $field);
}
