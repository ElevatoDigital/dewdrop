<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Auth\Page;

use Dewdrop\Auth\Db\UserPasswordChangeTokensTableGateway;

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
        $token = null;

        if ($this->request->get('token')) {
            $token = $this->findToken($this->request->get('token'));
        }

        $this->view->assign(
            array(
                'error'        => $this->app['security.last_error']($this->request),
                'lastUsername' => $this->app['session']->get('_security.last_username'),
                'token'        => $token
            )
        );

        return $this->renderLayout($this->view->render('login.phtml'));
    }

    private function findToken($token)
    {
        $tokenTable = new UserPasswordChangeTokensTableGateway();

        return $tokenTable->fetchRow(
            $tokenTable
                ->select()
                ->from($tokenTable->getTableName())
                ->where('token = ?', $token)
                ->where('used')
        );
    }
}
