<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Auth\Page;

/**
 * Login page
 */
class Login extends PageAbstract
{
    /**
     * Provides a string response to the request
     *
     * @return string
     */
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
