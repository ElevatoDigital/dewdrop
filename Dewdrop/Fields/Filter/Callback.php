<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Filter;

use Dewdrop\Fields;

/**
 * A filter that uses a custom callback to determine which fields should be
 * accepted.  Your callback will be given be called one time for each field,
 * with a field passed as the first argument to your callback each time.  If
 * you return true, the field will be included.  Otherwise, it will not.
 */
class Callback implements FilterInterface
{
    /**
     * The callback that should be used for filtering.
     *
     * @var callable
     */
    private $callback;

    /**
     * Provide the callback that will be used for filtering.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Filter the supplied Fields object using the filter's callback.
     *
     * @param Fields $fields
     * @return Fields
     */
    public function apply(Fields $fields)
    {
        $filteredFields = new Fields([], $fields->getUser());

        foreach ($fields as $field) {
            if (true === call_user_func($this->callback, $field)) {
                $filteredFields->add($field);
            }
        }

        return $filteredFields;
    }
}
