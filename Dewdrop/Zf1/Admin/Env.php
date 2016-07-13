<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1\Admin;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Env\EnvAbstract;
use Zend\View\Helper\HeadLink;
use Zend\View\Helper\HeadScript;
use Zend_Controller_Action;

class Env extends EnvAbstract
{
    /**
     * @var Zend_Controller_Action
     */
    private $actionController;

    public function __construct(Zend_Controller_Action $actionController)
    {
        $this->actionController = $actionController;
    }

    public function renderLayout($content, HeadScript $headScript = null, HeadLink $headLink = null)
    {
        echo $content;

        foreach ($this->coreClientSideDependencies['js'] as $src) {
            $this->actionController->view->headScript()->appendFile('/bower_components' . $src);
        }

        foreach ($this->coreClientSideDependencies['css'] as $href) {
            $this->actionController->view->headLink()->appendStylesheet('/bower_components' . $href);
        }

        foreach ($headScript as $script) {
            $this->actionController->view->headScript()->appendFile($script->attributes['src']);
        }

        foreach ($headLink as $link) {
            if ('stylesheet' === $link->rel) {
                $this->actionController->view->headLink()->appendStylesheet($link->href);
            }
        }
    }

    public function url(ComponentInterface $component, $page, array $params = array())
    {
        // TODO: Implement url() method.
    }

    public function initComponent(ComponentInterface $component)
    {
        // TODO: Implement initComponent() method.
    }

    public function redirect($url)
    {
        /* @var $redirector \Zend_Controller_Action_Helper_Redirector */
        $redirector = $this->actionController->getHelper('Redirector');
        $redirector->gotoUrlAndExit($url);
    }

}
