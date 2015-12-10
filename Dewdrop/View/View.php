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
use Dewdrop\Pimple;
use Dewdrop\Request;
use Dewdrop\View\Helper\PageDelegateInterface;
use Zend\Escaper\Escaper;

// Solely for static analysis assistance
use Dewdrop\Fields\Helper\TableCell;
use Dewdrop\Fields\Listing\BulkActions;
use Dewdrop\View\Helper;
use Dewdrop\View\Helper\BulkActionCheckboxField;

/**
 * A simple view implementation that allows for simple assignment of data,
 * escaping for common contexts (e.g. HTML, HTML attribute, JS, etc.),
 * and calling of helper objects for reusable view logic.
 *
 * Including method annotations here (http://www.phpdoc.org/docs/latest/references/phpdoc/tags/method.html)
 * to assist static analysis in PHPStorm and Scrutinizer CI given the use
 * of the __call() magic method in this class for view helpers.  Won't cover
 * us on custom helpers necessarily, but it will help catch most bugs/typos.
 *
 * @method string adminComponentNav()
 * @method string adminNotice()
 * @method string adminUrl()
 * @method string bootstrapColumnsModal()
 * @method string bootstrapDetailsView()
 * @method mixed bootstrapFilterForm()
 * @method mixed bootstrapForm()
 * @method string bootstrapInputText()
 * @method Helper\BootstrapRowActions bootstrapRowActions()
 * @method string bootstrapSelect()
 * @method mixed bootstrapTable()
 * @method string bootstrapTextarea()
 * @method string bowerUrl(string $url, string $wwwPath = null, string $docRoot = null)
 * @method Helper\BulkActionForm bulkActionForm()
 * @method BulkActionCheckboxField bulkActionCheckboxField(BulkActions $bulkActions, TableCell $tableCellRenderer)
 * @method string checkboxList()
 * @method \Dewdrop\Fields\Helper\CsvCell csvCellRenderer()
 * @method string csvExport()
 * @method Helper\DetectEditHelper detectEditHelper()
 * @method Helper\EditForm editForm()
 * @method \Dewdrop\Fields\Helper\EditControl editControlRenderer()
 * @method \Zend\View\Helper\HeadLink headLink()
 * @method \Zend\View\Helper\HeadMeta headMeta()
 * @method \Zend\View\Helper\HeadScript headScript()
 * @method \Zend\View\Helper\HeadStyle headStyle()
 * @method void inlineScript()
 * @method string inputCheckbox()
 * @method string inputText()
 * @method string inputTimestamp()
 * @method string pagination()
 * @method string select()
 * @method string summernote()
 * @method mixed table()
 * @method \Dewdrop\Fields\Helper\TableCell tableCellRenderer()
 * @method Helper\TableSortHandle tableSortHandle()
 * @method string textarea()
 * @method mixed urlCachePrefix()
 * @method string wpAdminNotice()
 * @method string wpCheckboxList()
 * @method string wpColorPicker()
 * @method Helper\WpEditForm wpEditForm()
 * @method string wpEditor()
 * @method Helper\WpEditRow wpEditRow()
 * @method string wpImagePicker()
 * @method string wpInputCheckbox()
 * @method string wpInputText()
 * @method string wpSelect()
 * @method mixed wpTable()
 * @method Helper\WpWrap wpWrap()
 * @method Helper\Wrap wrap()
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
    private $internalViewData = array();

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
     * The current HTTP request.
     *
     * @var \Dewdrop\Request
     */
    private $request;

    /**
     * The available helper names and their associated classes.
     *
     * @var array
     */
    private $helperClasses = array(
        'admincomponentnav'        => '\Dewdrop\View\Helper\AdminComponentNav',
        'adminfooter'              => '\Dewdrop\View\Helper\AdminFooter',
        'adminnotice'              => '\Dewdrop\View\Helper\AdminNotice',
        'admintitle'               => '\Dewdrop\View\Helper\AdminTitle',
        'adminurl'                 => '\Dewdrop\View\Helper\AdminUrl',
        'bootstraptable'           => '\Dewdrop\View\Helper\BootstrapTable',
        'bootstrapbreadcrumbs'     => '\Dewdrop\View\Helper\BootstrapBreadcrumbs',
        'bootstrapcolumnsmodal'    => '\Dewdrop\View\Helper\BootstrapColumnsModal',
        'bootstrapdetailsview'     => '\Dewdrop\View\Helper\BootstrapDetailsView',
        'bootstrapfilterform'      => '\Dewdrop\View\Helper\BootstrapFilterForm',
        'bootstrapform'            => '\Dewdrop\View\Helper\BootstrapForm',
        'bootstrapinputtext'       => '\Dewdrop\View\Helper\BootstrapInputText',
        'bootstraprowactions'      => '\Dewdrop\View\Helper\BootstrapRowActions',
        'bootstrapselect'          => '\Dewdrop\View\Helper\BootstrapSelect',
        'bootstraptextarea'        => '\Dewdrop\View\Helper\BootstrapTextarea',
        'bowerurl'                 => '\Dewdrop\View\Helper\BowerUrl',
        'bulkactionform'           => '\Dewdrop\View\Helper\BulkActionForm',
        'bulkactioncheckboxfield'  => '\Dewdrop\View\Helper\BulkActionCheckboxField',
        'cascadeselect'            => '\Dewdrop\View\Helper\CascadeSelect',
        'checkboxlist'             => '\Dewdrop\View\Helper\CheckboxList',
        'csvcellrenderer'          => '\Dewdrop\View\Helper\CsvCellRenderer',
        'csvexport'                => '\Dewdrop\View\Helper\CsvExport',
        'detectedithelper'         => '\Dewdrop\View\Helper\DetectEditHelper',
        'editform'                 => '\Dewdrop\View\Helper\EditForm',
        'editcontrolrenderer'      => '\Dewdrop\View\Helper\EditControlRenderer',
        'headlink'                 => '\Zend\View\Helper\HeadLink',
        'headmeta'                 => '\Zend\View\Helper\HeadMeta',
        'headscript'               => '\Zend\View\Helper\HeadScript',
        'headstyle'                => '\Zend\View\Helper\HeadStyle',
        'importeditcontrol'        => '\Dewdrop\View\Helper\ImportEditControl',
        'inlinescript'             => '\Dewdrop\View\Helper\InlineScript',
        'inputcheckbox'            => '\Dewdrop\View\Helper\InputCheckbox',
        'inputdate'                => '\Dewdrop\View\Helper\InputDate',
        'inputfile'                => '\Dewdrop\View\Helper\InputFile',
        'inputimage'               => '\Dewdrop\View\Helper\InputImage',
        'inputtext'                => '\Dewdrop\View\Helper\InputText',
        'inputtimestamp'           => '\Dewdrop\View\Helper\InputTimestamp',
        'optioninputdecorator'     => '\Dewdrop\View\Helper\OptionInputDecorator',
        'pagination'               => '\Dewdrop\View\Helper\Pagination',
        'rowcollectioninputtable'  => '\Dewdrop\View\Helper\RowCollectionInputTable',
        'rowcollectioncellcontent' => '\Dewdrop\View\Helper\RowCollectionCellContent',
        'select'                   => '\Dewdrop\View\Helper\Select',
        'summernote'               => '\Dewdrop\View\Helper\Summernote',
        'table'                    => '\Dewdrop\View\Helper\Table',
        'tablecellrenderer'        => '\Dewdrop\View\Helper\TableCellRenderer',
        'tablesorthandle'          => '\Dewdrop\View\Helper\TableSortHandle',
        'textarea'                 => '\Dewdrop\View\Helper\Textarea',
        'url'                      => '\Dewdrop\View\Helper\Url',
        'urlcacheprefix'           => '\Dewdrop\View\Helper\UrlCachePrefix',
        'wpadminnotice'            => '\Dewdrop\View\Helper\WpAdminNotice',
        'wpcheckboxlist'           => '\Dewdrop\View\Helper\WpCheckboxList',
        'wpcolorpicker'            => '\Dewdrop\View\Helper\WpColorPicker',
        'wpeditform'               => '\Dewdrop\View\Helper\WpEditForm',
        'wpeditor'                 => '\Dewdrop\View\Helper\WpEditor',
        'wpeditrow'                => '\Dewdrop\View\Helper\WpEditRow',
        'wpimagepicker'            => '\Dewdrop\View\Helper\WpImagePicker',
        'wpinputcheckbox'          => '\Dewdrop\View\Helper\WpInputCheckbox',
        'wpinputtext'              => '\Dewdrop\View\Helper\WpInputText',
        'wpselect'                 => '\Dewdrop\View\Helper\WpSelect',
        'wptable'                  => '\Dewdrop\View\Helper\WpTable',
        'wpwrap'                   => '\Dewdrop\View\Helper\WpWrap',
        'wrap'                     => '\Dewdrop\View\Helper\Wrap',
    );

    /**
     * Create a new view, optionally supplying an escaper object for use
     * in sanitizing output in various contexts.
     *
     * @param Escaper $escaper
     * @param Request $request
     */
    public function __construct(Escaper $escaper = null, Request $request = null)
    {
        $this->escaper = ($escaper ?: new Escaper());
        $this->request = ($request ?: Pimple::getResource('dewdrop-request'));

        $this->init();
    }

    /**
     * This method can be used by sub-classes to setup additional helpers, etc.
     *
     * @return void
     */
    public function init()
    {

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
     * @param string|array $name
     * @param mixed $value
     * @return \Dewdrop\View\View
     */
    public function assign($name, $value = null)
    {
        if (!is_array($name)) {
            $this->internalViewData[$name] = $value;
        } else {
            foreach ($name as $index => $value) {
                $this->assign($index, $value);
            }
        }

        return $this;
    }

    /**
     * Assign a helper instance to the supplied name, rather than requiring
     * that it be instantiated when the helper is accessed.  Typically used
     * to share helper instances with partials generated by a parent view,
     * which reduces resource usage and allows things like CSS and JS added
     * by partials to propagate intuitively.
     *
     * @param string $name
     * @param object $instance
     * @return $this
     */
    public function assignInstance($name, $instance)
    {
        $this->helpers[strtolower($name)] = $instance;

        return $this;
    }

    /**
     * Register a new helper name and class name.  This can be used to replace
     * a default helper implementation or to introduce a project-specific
     * helper.
     *
     * @param string $name
     * @param string $className The full class name/namespace.
     * @return \Dewdrop\View\View
     */
    public function registerHelper($name, $className)
    {
        $this->helperClasses[strtolower($name)] = $className;

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
        if (isset($this->internalViewData[$name])) {
            return $this->internalViewData[$name];
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
        return array_key_exists($name, $this->internalViewData);
    }

    /**
     * When calling an unknown method on this view, pass the method name to
     * the helper() method and call the helper's direct() method.  Using the
     * __call() magic method in this way allows using helpers in this manner:
     *
     * $this->helperName('arg1', $arg2);
     *
     * Rather than having to call an additional method on the helper like this:
     *
     * $this->helper('helperName')->direct('arg1', $arg2);
     *
     * If the direct() method is unavailable, the helper instance is returned instead.
     *
     * @param string $method
     * @param array $args
     * @return \Dewdrop\View\Helper\AbstractHelper
     */
    public function __call($method, $args)
    {
        $helper = $this->helper($method);

        if (method_exists($helper, 'direct')) {
            return call_user_func_array(array($helper, 'direct'), $args);
        } else {
            return $helper;
        }
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
     * @param string $scriptPath
     * @return string
     */
    public function partial($template, array $data, $scriptPath = null)
    {
        $partial = new View($this->escaper);

        $partial->assignInstance('headscript', $this->headScript());
        $partial->assignInstance('headlink', $this->headLink());

        // Pass along any custom helper class assignments to the newly created partial
        foreach ($this->helperClasses as $name => $className) {
            $partial->registerHelper($name, $className);
        }

        foreach ($this->helpers as $name => $helper) {
            if ($helper instanceof PageDelegateInterface) {
                /* @var $partialHelper PageDelegateInterface */
                $partialHelper = $partial->helper($name);

                $partialHelper->setPage($helper->getPage());
            }
        }

        $partial
            ->setScriptPath($scriptPath ?: $this->scriptPath)
            ->assign($data);

        return $partial->render($template);
    }

    /**
     * Get the current Request object for access to GET or POST data
     * from helpers.
     *
     * @return \Dewdrop\Request
     */
    public function getRequest()
    {
        return $this->request;
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
