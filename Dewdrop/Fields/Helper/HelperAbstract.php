<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\Exception\HelperCallableNotAvailableForField;
use Dewdrop\Fields\Exception\HelperMustHaveName;
use Dewdrop\Fields\FieldInterface;

/**
 * Helpers in the Dewdrop Fields API allow you to inject custom code into
 * loops and other structures that would normally be tricky to customize.
 * Helpers do this by enabling your to set custom callbacks for a field.
 * In cases where a custom callback has not been assigned, helpers may
 * attempt to automatically create a callback, using information in the
 * field (e.g. the type of a database-related field).
 *
 * For all helpers, you can assign custom callbacks globally or on a
 * per-instance basis.  Here's an example of defining a custom callback
 * globally for a field:
 *
 * <pre>
 * $field->assignHelperCallback(
 *     'NameOfHelperToCustomize',
 *     function ($helper) {
 *         // Any custom logic you'd like to perform for this field.
 *     }
 * );
 * </pre>
 *
 * In the above example, "NameOfHelperToCustomize" is the name of the helper
 * as defined in the helper's $name class property.  This string is case
 * insensitive.
 *
 * To defined a custom callback on a per-instance basis for a helper, you
 * can do the following:
 *
 * <pre>
 * $helper->assign(
 *     'my_model:field_id',
 *     function ($helper) {
 *         // Any custom logic you'd like to perform for this field.
 *     }
 * );
 * </pre>
 *
 * If you'd like to decorate an existing callback with additional logic,
 * that's possible using the getFieldAssignment() method:
 *
 * <pre>
 * $field->assignHelperCallback(
 *     'NameOfHelperToCustomize',
 *     function ($helper) {
 *         return '<strong>' . call_user_func($helper->getFieldAssignment($field), $helper) . '</strong>';
 *     }
 * );
 * </pre>
 *
 * When a per-instance callback is assigned, that overrides any global or
 * fallback callbacks.  So, you can have an application-wide default (e.g.
 * in a model) behavior for a field that is superseded in specific cases by
 * adding a per-instance callback assignment for the field (e.g. in a view
 * script).
 */
abstract class HelperAbstract implements HelperInterface
{
    /**
     * The name for this helper, used when you want to define a global custom
     * callback for a given field
     *
     * @see \Dewdrop\Fields\FieldInterface::assignHelperCallback()
     * @var string
     */
    protected $name;

    /**
     * Any per-instance callback assignments for this helper.  This array will
     * have field IDs as the keys and callables as the values.
     *
     * @var array
     */
    private $assignments = array();

    /**
     * Check to see if the supplied input matches this helper's name
     * (case-insensitive).
     *
     * @param string $name
     * @return boolean
     */
    public function matchesName($name)
    {
        return $this->name === strtolower($name);
    }

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
    public function assign($assignments, callable $callable = null)
    {
        if (!is_array($assignments)) {
            if ($assignments instanceof FieldInterface) {
                $assignments = $assignments->getId();
            }

            $assignments = array($assignments => $callable);
        }

        foreach ($assignments as $id => $callable) {
            if (!is_callable($callable)) {
                throw new Exception('Must pass a valid callable as the second parameter');
            }

            $this->assignments[$id] = $this->wrapCallable($callable);
        }

        return $this;
    }

    /**
     * Get the callback that will be used for the given FieldInterface object.
     *
     * @param FieldInterface $field
     * @throws \Dewdrop\Fields\Exception\HelperCallableNotAvailableForField
     * @return callable
     */
    public function getFieldAssignment(FieldInterface $field)
    {
        if (!$this->hasValidName()) {
            return false;
        }

        $id = $field->getId();

        if (!array_key_exists($id, $this->assignments)) {
            if (!$field->hasHelperCallback($this->name)) {
                $callback = $this->detectCallableForField($field);
            } else {
                $callback = $field->getHelperCallback($this->name);
            }

            if (!is_callable($callback)) {
                throw new HelperCallableNotAvailableForField(
                    "Field {$id} does not have a callable assigned and one could not be detected."
                );
            }

            $this->assignments[$id] = $this->wrapCallable($callback);
        }

        return $this->assignments[$id];
    }

    /**
     * Ensure the helper has a valid $name value.  If not, throw an exception.
     * All helpers must have a name defined so that global custom callbacks
     * can be added to fields.
     *
     * @throws \Dewdrop\Fields\Exception\HelperMustHaveName
     * @return boolean
     */
    public function hasValidName()
    {
        if (!$this->name) {
            throw new HelperMustHaveName(
                'Assign a lower-case string with no spaces and dots as word separators as your helper\'s $name'
            );
        }

        return true;
    }

    /**
     * Try to supply a default callback by looking at the supplied
     * FieldInterface object.  This method will only be called for a
     * field if no global or per-instance custom callbacks are assigned.
     * If no callback candidate is found, just return false from this method,
     * which will be detected by getFieldAssignment(), causing execution to
     * halt.
     *
     * @param FieldInterface $field
     * @return mixed Either false or a callable.
     */
    abstract public function detectCallableForField(FieldInterface $field);

    /**
     * Wrap a field's callback to ensure that a reference to the helper is
     * always supplied as the first argument to the callback.
     *
     * @param callable $callable
     * @return callable
     */
    protected function wrapCallable(callable $callable)
    {
        return function () use ($callable) {
            $arguments = func_get_args();

            array_unshift($arguments, $this);

            return call_user_func_array($callable, $arguments);
        };
    }
}
