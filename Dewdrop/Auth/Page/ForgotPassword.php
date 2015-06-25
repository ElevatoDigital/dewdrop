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
 * Forgotten password page
 */
class ForgotPassword extends PageAbstract
{
    /**
     * Provides a response to the request
     *
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function respond()
    {
        if ($this->request->isMethod('POST')) {
            /* @var $usersTable \Dewdrop\Auth\Db\UsersTableGateway */
            $usersTable = $this->app['users-gateway'];

            $emailAddress = $this->request->request->get('email_address');

            $user = $usersTable->loadUserByEmailAddress($emailAddress);

            if (null !== $user) {
                /* @var $auth \Dewdrop\Auth */
                $auth = $this->app['auth'];
                $auth->forgotPassword($user);
            }

            return $this->app->redirect('/auth/forgot-password?email_address=' . rawurlencode($emailAddress));
        }

        $emailAddress = $this->request->query->get('email_address');

        if (null !== $emailAddress) {
            $this->view->assign([
                'email_address' => $emailAddress,
            ]);
        }

        return $this->renderLayout($this->view->render('forgot-password-form.phtml'));
    }
}
