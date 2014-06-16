<?php

namespace Dewdrop\Admin\Component;

use Dewdrop\Admin\ComponentAbstract;
use Dewdrop\Admin\Response;
use Dewdrop\Exception;
use Dewdrop\Pimple as DewdropPimple;
use ReflectionClass;
use Pimple;
use Silex\Application as SilexApplication;

abstract class Silex extends ComponentAbstract
{
    protected $admin;

    protected $application;

    public function __construct(Pimple $pimple = null, $componentName = null)
    {
        $this->pimple      = ($pimple ?: DewdropPimple::getInstance());
        $this->admin       = $this->pimple['admin'];
        $this->application = $this->pimple;

        parent::__construct($this->pimple, $componentName);

        $this->redirector = function ($url) {
            return $this->application->redirect($url);
        };
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function addSubmenuDivider()
    {
        $this->submenuPages[] = array(
            'isDivider' => true
        );

        return $this;
    }

    public function url($page, array $params = array())
    {
        return '/admin/'
            . $this->getName() . '/'
            . $this->application['inflector']->hyphenize($page)
            . $this->assembleQueryString($params, '?');
    }

    public function register()
    {
        $this->application->match(
            '/admin/' . $this->getName() . '/{page}',
            function ($page) {
                $response = new Response();
                $page     = $this->createPageObject($page);
                $redirect = $this->dispatchPage($page, $response);

                if ($redirect) {
                    return $redirect;
                }

                $output = $response->getOutput();

                if (!$this->shouldRenderLayout || $this->request->isAjax()) {
                    return $output;
                } else {
                    return $this->admin->renderLayout($output, $page->getView()->headScript());
                }
            }
        )
        ->value('page', 'index');
    }
}

