<?php

namespace Dewdrop\View;

use Dewdrop\Exception;
use Zend\Escaper\Escaper;

class View
{
    private $data = array();

    private $helpers = array();

    private $scriptPath;

    private $helperClasses = array(
        'wpeditform'      => '\Dewdrop\View\Helper\WpEditForm',
        'wptitle'         => '\Dewdrop\View\Helper\WpTitle',
        'wpeditrow'       => '\Dewdrop\View\Helper\WpEditRow',
        'wpinputtext'     => '\Dewdrop\View\Helper\WpInputText',
        'wpinputcheckbox' => '\Dewdrop\View\Helper\WpInputCheckbox'
    );

    public function __construct(Escaper $escaper = null)
    {
        $this->escaper = ($escaper ?: new Escaper());
    }

    public function assign($name, $value = null)
    {
        if (!is_array($name)) {
            $this->data[$name] = $value;
        } else {
            foreach ($name as $index => $value) {
                $this->assign($index, $value);
            }
        }
    }

    public function __get($name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    public function __set($name, $value)
    {
        $this->assign($name, $value);

        return $this;
    }

    public function __call($method, $args)
    {
        $helper = $this->helper($method);

        return call_user_func_array(array($helper, 'direct'), $args);
    }

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

    public function getEscaper()
    {
        return $this->escaper;
    }

    public function setScriptPath($path)
    {
        $this->scriptPath = realpath($path);

        return $this;
    }

    public function render($template)
    {
        ob_start();
        require $this->scriptPath . '/' . basename($template);
        return ob_get_clean();
    }

    public function escapeHtml($string)
    {
        if (null === $string) {
            $string = '';
        }

        return $this->escaper->escapeHtml($string);
    }

    public function escapeHtmlAttr($string)
    {
        if (null === $string) {
            $string = '';
        }

        return $this->escaper->escapeHtmlAttr($string);
    }

    public function escapeJs($string)
    {
        if (null === $string) {
            $string = '';
        }

        return $this->escaper->escapeJs($string);
    }

    public function escapeUrl($string)
    {
        if (null === $string) {
            $string = '';
        }

        return $this->escaper->escapeUrl($string);
    }

    public function escapeCss($string)
    {
        if (null === $string) {
            $string = '';
        }

        return $this->escaper->escapeCss($string);
    }

    private function instantiateHelper($name)
    {
        $className = $this->helperClasses[$name];
        $helper    = new $className($this);

        $this->helpers[$name] = $helper;

        return $helper;
    }
}
