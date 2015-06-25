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
use Dewdrop\Exception;
use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;

/**
 * Allow users to modify a listing's Select using a callback.  Can be useful
 * if you don't need all the stock filtering and sorting logic for your specific
 * case.
 */
class SelectCallback extends HelperAbstract implements SelectModifierInterface
{
    /**
     * The name for this custom callback.  Unlike others, this is defined at runtime
     * for callback modifiers, not in the code.
     *
     * @var string
     */
    protected $name;

    /**
     * The prefix used by the listing.
     *
     * @var string
     */
    protected $prefix;

    /**
     * The callback to call when applying the modifier.
     *
     * @var callable
     */
    protected $callback;

    /**
     * Set the name for this modifier.  Primarily useful if you want to retrieve
     * the modifier from the listing after initially registering it.
     *
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the callback you'd like to use to modify the listing's Select object.
     *
     * @param callable $callback
     * @return $this
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set the param prefix used by the listing.  Useful if you need to use your
     * custom modifier with multiple listings.
     *
     * @param $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Get the prefix for the listing parameters.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Note that when we call this particular modifier's callback, we only pass
     * the Select object, not the fields or any other information.  If you need
     * access to that info, arrange for it in the code where you're defining the
     * callback in the first place.  The common case only needs the Select itself
     * in the callback, so we optimize for that.
     *
     * @param Fields $fields
     * @param Select $select
     * @return Select|mixed
     * @throws \Dewdrop\Exception
     */
    public function modifySelect(Fields $fields, Select $select)
    {
        $select = call_user_func($this->callback, $select);

        if (!$select instanceof Select) {
            throw new Exception('Your callback must return a Select object.');
        }

        return $select;
    }

    /**
     * No per-field callbacks in this case, really.
     *
     * @param FieldInterface $field
     * @return bool|mixed
     */
    public function detectCallableForField(FieldInterface $field)
    {
        return false;
    }
}
