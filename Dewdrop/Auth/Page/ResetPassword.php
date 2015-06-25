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
use Dewdrop\Auth\Db\UsersTableGateway;
use Dewdrop\Exception;
use Dewdrop\Pimple;

/**
 * Reset password page
 */
class ResetPassword extends PageAbstract
{
    /**
     * Provides a response to the request
     *
     * @return string|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws Exception
     */
    public function respond()
    {
        $request = Pimple::getResource('dewdrop-request');

        if ($request->isPost()) {
            if (6 > strlen($request->getPost('password'))) {
                $this->view->assign('error', 'Password must be at least 6 characters long.');
            } elseif ($request->getPost('password') !== $request->getPost('confirm')) {
                $this->view->assign('error', 'Passwords do not match.');
            } else {
                $userAndToken = $this->getUserAndTokenRows($this->request->query->get('token'));

                $userAndToken['token']
                    ->set('used', 1)
                    ->save();

                $userAndToken['user']
                    ->hashPassword($request->getPost('password'))
                    ->save();

                return $this->app->redirect('/auth/login?token=' . $userAndToken['token']->get('token'));
            }
        }

        try {
            $userAndToken = $this->getUserAndTokenRows($this->request->query->get('token'));

            $this->view->assign('user', $userAndToken['user']);
        } catch (Exception $e) {
            $this->view->assign('invalidToken', true);
        }

        $this->view
            ->assign('password', $request->getPost('password'))
            ->assign('confirm', $request->getPost('confirm'));

        return $this->renderLayout($this->view->render('reset-password.phtml'));
    }

    /**
     * Returns an array of the user and token rows for the given token string, or throws an Exception on failure
     *
     * @param string $token
     * @return array
     * @throws \Dewdrop\Exception
     */
    protected function getUserAndTokenRows($token)
    {
        do {
            if (null === $token) {
                break;
            }

            $tokenTable = new UserPasswordChangeTokensTableGateway();

            $tokenRow = $tokenTable->fetchRow(
                $tokenTable
                    ->select()
                    ->from($tokenTable->getTableName())
                    ->where('token = ?', $token)
                    ->where('NOT used')
            );

            if (null === $tokenRow) {
                break;
            }

            $usersTable = new UsersTableGateway();
            $userRow    = $usersTable->find($tokenRow->get('user_id'));

            return [
                'token' => $tokenRow,
                'user'  => $userRow,
            ];
        } while (false);

        throw new Exception('Invalid token');
    }
}
