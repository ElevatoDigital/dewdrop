<?php

namespace Dewdrop\View\Helper;

use Dewdrop\View\View;
use Dewdrop\Db\Field;

/**
 *
 */
abstract class AbstractHelper
{
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function direct()
    {
        return $this;
    }

    public function partial($name, array $data)
    {
        $view = new View($this->view->getEscaper());

        $view
            ->setScriptPath(__DIR__ . '/partials')
            ->assign($data);

        return $view->render($name);
    }

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

    protected function ensurePresent(array &$options, array $present)
    {
        foreach ($present as $option) {
            if (!array_key_exists($option, $options)) {
                $options[$option] = null;
            }
        }

        return $this;
    }

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
