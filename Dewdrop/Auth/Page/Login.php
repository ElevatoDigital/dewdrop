<?php

namespace Dewdrop\Auth\Page;

class Login extends PageAbstract
{
    public function respond()
    {
        $this->view->assign(
            array(
                'error'        => $this->app['security.last_error']($this->request),
                'lastUsername' => $this->app['session']->get('_security.last_username')
            )
        );

        return $this->renderLayout($this->view->render('login.phtml'));
    }
}
