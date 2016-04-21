<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Db\Field;
use Dewdrop\Exception;
use Dewdrop\View\View;

/**
 * A base class for view helpers.
 *
 * This class provides:
 *
 * <ul>
 *     <li>A reference to the view that originally instantiated the helper.</li>
 *     <li>The ability to easily render partial view scripts.</li>
 *     <li>
 *         The ability to manage methods implementing the 3 Dewdrop view helper
 *         argument styles (i.e. Field, explicit and array).
 *     </li>
 * </ul>
 */
abstract class AbstractHelper
{
    /**
     * The view that instantiated this helper.
     *
     * @var \Dewdrop\View\View
     */
    protected $view;

    /**
     * Create helper, accepting a reference to the related view object
     *
     * @param View $view
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Render a partial view script.
     *
     * Generally, your helper should render HTML with partial view scripts
     * rather than generating the markup in the helper class directly.  This
     * makes it easier for frontend developers to make modifications to the HTML.
     *
     * The $data parameter should contain key-value pairs for each variable you'd
     * like available in your partial view.
     *
     * @param string $name
     * @param array $data
     * @return string The rendered output
     */
    public function partial($name, array $data)
    {
        return $this->view->partial($name, $data, __DIR__ . '/partials');
    }

    /**
     * Useful in cases where you want to just return the helper if the user didn't
     * provide any args to direct(), but want to call a short-cut method if they
     * did.
     *
     * @param array $args
     * @param string $method
     * @return $this|string
     */
    protected function delegateIfArgsProvided(array $args, $method = 'directWithArgs')
    {
        if (0 === count($args)) {
            return $this;
        } else {
            return call_user_func_array([$this, $method], $args);
        }
    }

    /**
     * Delegate to one of three methods depending upon the contents of the $args
     * array:
     *
     * - If $args[0] is an instance of \Dewdrop\Db\Field,
     *   call "{$methodPrefix}Field".
     * - If $args[0] is an array, call "{$methodPrefix}Array".
     * - Otherwise, call "{$methodPrefix}Explicit".
     *
     * @param array $args The arguments to pass the delegated method.
     * @param string $methodPrefix
     * @return mixed
     */
    protected function delegateByArgs(array $args, $methodPrefix)
    {
        if (isset($args[0]) && $args[0] instanceof Field) {
            $methodName = "{$methodPrefix}Field";
        } elseif (isset($args[0]) && is_array($args[0])) {
            $methodName = "{$methodPrefix}Array";
        } else {
            $methodName = "{$methodPrefix}Explicit";
        }

        return call_user_func_array(
            array($this, $methodName),
            $args
        );
    }

    /**
     * Check that the values in $required are present as keys in $options.
     *
     * Use this in methods accepting parameters as an array of key-value pairs
     * to ensure that required parameters are present.  If one of the required
     * parameters is absent, an exception is thrown.
     *
     * @param array $options
     * @param array $required
     * @throws \Dewdrop\Exception
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    protected function checkRequired(array $options, array $required)
    {
        foreach ($required as $option) {
            if (!array_key_exists($option, $options)) {
                throw new Exception(
                    'Option "' . $option . '" is required for ' . $this->getHelperName() . '.'
                );
            }
        }

        return $this;
    }

    /**
     * Get the helper name, which is the suffix at the end of the fully
     * qualified class name after the final namespace separator ("\").
     *
     * @return string
     */
    protected function getHelperName()
    {
        $className = get_class($this);

        return substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * Ensure that the values in $present are keys in $options.
     *
     * If the key is absent from $options, it will be added with a null value.
     * Therefore, this method differs from checkRequired() in that the user
     * _must_ supply a value (even if that value is null) before checkRequired(),
     * but for ensurePresent() the key will simply be added if the user hadn't
     * already specified a value.
     *
     * Note: Notice that the $options parameter is handled by-reference to allow
     * creation of the missing keys while still allowing chaining to other
     * methods.
     *
     * @param array $options
     * @param array $present
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    protected function ensurePresent(array &$options, array $present)
    {
        foreach ($present as $option) {
            if (!array_key_exists($option, $options)) {
                $options[$option] = null;
            }
        }

        return $this;
    }

    /**
     * Ensure that the values in the $isArray parameter are present in $options
     * as an array.
     *
     * If the option's current value is null, it will be converted to an array.
     * If the option's current value is not null but also not an array, it will
     * be wrapped in an array.  For example, if you had a "classes" option that
     * let the user specify one or more CSS classes, they could use a string
     * to define a single class and this method would wrap that single value in
     * array array to make the handling of the various options simpler and more
     * consistent for the view helper developer.
     *
     * Note: Notice that the $options parameter is handled by-reference to allow
     * creation of the missing keys while still allowing chaining to other
     * methods.
     *
     * @param array $options
     * @param array $isArray
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    protected function ensureArray(array &$options, array $isArray)
    {
        foreach ($isArray as $arrayOption) {
            if (!$options[$arrayOption]) {
                $options[$arrayOption] = array();
            } elseif (!is_array($options[$arrayOption])) {
                $options[$arrayOption] = array($options[$arrayOption]);
            }
        }

        return $this;
    }
}
