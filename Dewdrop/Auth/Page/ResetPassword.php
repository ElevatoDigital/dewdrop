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
        if ($this->request->isMethod('POST')) {

            $rows = $this->getUserAndTokenRows($this->request->request->get('token'));

            // @todo Process form submission

//            $rows['token']
//                ->set('used', true)
//                ->save();

//            return $this->app->redirect('/target');
        }

        $this->getUserAndTokenRows($this->request->query->get('token'));

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

            $userRow = $usersTable->find($tokenRow->get('user_id'));

            return [
                'token' => $tokenRow,
                'user'  => $userRow,
            ];

        } while (false);

        throw new Exception('Invalid token');
    }
}