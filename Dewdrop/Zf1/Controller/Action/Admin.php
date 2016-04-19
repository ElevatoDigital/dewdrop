<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1\Controller\Action;

use Dewdrop\Admin\Component\ComponentInterface;
use Dewdrop\Admin\Component\ComponentTrait;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\PageFactory\Crud as CrudFactory;
use Dewdrop\Zf1\Admin\Env as Zf1AdminEnv;
use Dewdrop\Pimple;
use Zend_Controller_Action;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Response_Abstract;

class Admin extends Zend_Controller_Action implements ComponentInterface
{
    use ComponentTrait;

    private $pimple;

    private $title;

    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response,
        array $invokeArgs = array()
    ) {
        $this->pimple = Pimple::getInstance();

        if ($this instanceof CrudInterface) {
            $this->addPageFactory(new CrudFactory($this));
        }

        $this->env = new Zf1AdminEnv($this);

        parent::__construct($request, $response, $invokeArgs);
    }

    public function __call($methodName, $args)
    {
        $this->_helper->viewRenderer->setNoRender();

        $pageName = preg_replace('/Action$/', '', $methodName);
        $this->dispatchPage($pageName);

        // parent::__call($methodName, $args);
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        if (!$this->title && property_exists(get_class($this), 'componentTitle')) {
            $this->title = static::$componentTitle;
        }

        return $this->title;
    }

    public function getName()
    {
        return $this->_request->getControllerName();
    }

    public function hasPimpleResource($name)
    {
        return isset($this->pimple[$name]);
    }

    public function getPimpleResource($name)
    {
        return $this->pimple[$name];
    }

    public function url($page, array $params = [])
    {
        $url = $this->_helper->url($page);

        if (count($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}
