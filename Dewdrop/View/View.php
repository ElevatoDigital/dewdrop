<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View;

use Dewdrop\Exception;
use Zend\Escaper\Escaper;

/**
 * A simple view implementation that allows for simple assignment of data,
 * escaping for common contexts (e.g. HTML, HTML attribute, JS, etc.),
 * and calling of helper objects for reusable view logic.
 */
class View
{
    /**
     * The data assigned to this view.  Usually, a controller will be
     * responsible for passing data to the view object using the assign()
     * method.
     *
     * @var array
     */
    private $data = array();

    /**
     * Helper instances created by calls to instantiateHelper().
     *
     * @var array
     */
    private $helpers = array();

    /**
     * The path in which to look for view scripts.
     *
     * @var string
     */
    private $scriptPath;

    /**
     * The available helper names and their associated classes.
     *
     * @var array
     */
    private $helperClasses = array(
        'adminurl'         => '\Dewdrop\View\Helper\AdminUrl',
        'detectedithelper' => '\Dewdrop\View\Helper\DetectEditHelper',
        'inlinescript'     => '\Dewdrop\View\Helper\InlineScript',
        'textarea'         => '\Dewdrop\View\Helper\Textarea',
        'ui'               => '\Dewdrop\View\Helper\Ui',
        'wpadminnotice'    => '\Dewdrop\View\Helper\WpAdminNotice',
        'wpcheckboxlist'   => '\Dewdrop\View\Helper\WpCheckboxList',
        'wpcolorpicker'    => '\Dewdrop\View\Helper\WpColorPicker',
        'wpeditform'       => '\Dewdrop\View\Helper\WpEditForm',
        'wpeditor'         => '\Dewdrop\View\Helper\WpEditor',
        'wpeditrow'        => '\Dewdrop\View\Helper\WpEditRow',
        'wpinputtext'      => '\Dewdrop\View\Helper\WpInputText',
        'wpinputcheckbox'  => '\Dewdrop\View\Helper\WpInputCheckbox',
        'wpselect'         => '\Dewdrop\View\Helper\WpSelect',
        'wptitle'          => '\Dewdrop\View\Helper\WpTitle',
        'wpwrap'           => '\Dewdrop\View\Helper\WpWrap'
    );

    /**
     * Create a new view, optionally supplying an escaper object for use
     * in sanitizing output in various contexts.
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper = null)
    {
        $this->escaper = ($escaper ?: new Escaper());
    }

    /**
     * Assign variables to this view's data.
     *
     * If the $name parameter is an array rather than a string, assign() will
     * iterate over it, assigning variables for each key-value pair.  For
     * example, passing the following array would assign 3 different data
     * variables:
     *
     * $this->assign(
     *     array(
     *         'var1' => 1,
     *         'var1' => 2,
     *         'var1' => 3
     *     )
     * );
     *
     * @param string $name
     * @param mixed $value
     * @return \Dewdrop\View\View
     */
    public function assign($name, $value = null)
    {
        if (!is_array($name)) {
            $this->data[$name] = $value;
        } else {
            foreach ($name as $index => $value) {
                $this->assign($index, $value);
            }
        }

        return $this;
    }

    /**
     * Retrieve the named index from the $data property or return null.  This
     * makes it easier to avoid undefined variable notices in your view scripts
     * because you don't have to be quite so circumspect in ensuring variables
     * are set before checking them.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    /**
     * Delegate assignment to unknown class properties to the assign() method.
     *
     * @param string $name
     * @param mixed $value
     * @return \Dewdrop\View\View
     */
    public function __set($name, $value)
    {
        $this->assign($name, $value);

        return $this;
    }

    /**
     * Check to see if the given item is present in the view's data array.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * When calling an unkonwn method on this view, pass the method name to
     * the helper() method and call the helper's direct() method.  Using the
     * __call() magic method in this way allows using helpers in this manner:
     *
     * $this->helperName('arg1', $arg2);
     *
     * Rather than having to call an additional method on the helper like this:
     *
     * $this->helper('helperName')->direct('arg1', $arg2);
     *
     * @param string $method
     * @param array $args
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    public function __call($method, $args)
    {
        $helper = $this->helper($method);

        return call_user_func_array(array($helper, 'direct'), $args);
    }

    /**
     * Get the helper matching the provided named.
     *
     * @param string $name
     * @throws \Dewdrop\Exception
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    public function helper($name)
    {
        $name = strtolower($name);

        if (isset($this->helpers[$name])) {
            return $this->helpers[$name];
        } elseif (isset($this->helperClasses[$name])) {
            return $this->instantiateHelper($name);
        } else {
            throw new Exception("No helper with name \"{$name}\" could be found.");
        }
    }

    /**
     * Render a partial view.  By default, the same script path used by this
     * view is passed along.  This escaper from this view is also passed to
     * the partial.
     *
     * @param string $template
     * @param array $data
     * @return string
     */
    public function partial($template, array $data)
    {
        $partial = new View($this->escaper);

        $partial
            ->setScriptPath($this->scriptPath)
            ->assign($data);

        return $partial->render($template);
    }

    /**
     * The escaper is made accessible so that partial/nested views can reuse
     * the escaper object rather than having to make another.
     *
     * @return \Zend\Escaper\Escaper
     */
    public function getEscaper()
    {
        return $this->escaper;
    }

    /**
     * Set the path in which to look for view scripts.
     *
     * @param string $path
     * @return \Dewdrop\View\View
     */
    public function setScriptPath($path)
    {
        $this->scriptPath = realpath($path);

        return $this;
    }

    /**
     * Render the provided template file.
     *
     * The file's name should end in .phtml and be present in the assigned
     * script path.  This method returns the output as a string so that you
     * have the opportunity to filter or otherwise handle it prior to actually
     * adding it to the response.
     *
     * @param string $template
     * @return string
     */
    public function render($template)
    {
        ob_start();
        require $this->scriptPath . '/' . basename($template);
        return ob_get_clean();
    }

    /**
     * Escape string included in normal HTML context (i.e. not in an attribute value).
     *
     * @see \Zend\Escaper\Escaper::escapeHtml
     * @param string $string
     * @return string
     */
    public function escapeHtml($string)
    {
        return $this->escaper->escapeHtml(
            $this->castEscaperStringValue($string)
        );
    }

    /**
     * Escape a string included in an HTML attribute value.
     *
     * @see \Zend\Escaper\Escaper::escapeHtmlAttr
     * @param string $string
     * @return string
     */
    public function escapeHtmlAttr($string)
    {
        return $this->escaper->escapeHtmlAttr(
            $this->castEscaperStringValue($string)
        );
    }

    /**
     * Escape a JavaScript string.
     *
     * @see \Zend\Escaper\Escaper::escapeJs
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        return $this->escaper->escapeJs(
            $this->castEscaperStringValue($string)
        );
    }

    /**
     * This will encode the supplied input as JSON using flags that
     * make it safe to embed that JSON in an HTML context like an
     * inline script block.
     *
     * @param mixed $input
     * @return string
     */
    public function encodeJsonHtmlSafe($input)
    {
        return json_encode($input, JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS);
    }

    /**
     * Escape a URL.
     *
     * @see \Zend\Escaper\Escaper::escapeUrl
     * @param string $string
     * @return string
     */
    public function escapeUrl($string)
    {
        return $this->escaper->escapeUrl(
            $this->castEscaperStringValue($string)
        );
    }

    /**
     * Escape a CSS property value.
     *
     * @see \Zend\Escaper\Escaper::escapeCss
     * @param string $string
     * @return string
     */
    public function escapeCss($string)
    {
        return $this->escaper->escapeCss(
            $this->castEscaperStringValue($string)
        );
    }

    /**
     * Create an instance of helper associated with the provided name and
     * store that instance in the $helpers property so that it can be retrieved
     * on any subsequent calls.
     *
     * @param string $name
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    private function instantiateHelper($name)
    {
        $className = $this->helperClasses[$name];
        $helper    = new $className($this);

        $this->helpers[$name] = $helper;

        return $helper;
    }

    /**
     * \Zend\Escaper\Escaper does not play well with falsey non-string values
     * like null or false.  It throws exceptions claiming it cannot convert
     * them to utf-8.  This method will convert false and null to an empty
     * string and then cast the return value to a string (which should catch
     * ints and floats as well).
     *
     * @param mixed $input
     * @return string
     */
    private function castEscaperStringValue($input)
    {
        if (false === $input || null === $input) {
            $input = '';
        }

        return (string) $input;
    }
}
