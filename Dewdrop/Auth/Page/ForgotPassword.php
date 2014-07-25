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
     * Provides a string response to the request
     *
     * @return string
     */
    public function respond()
    {
        return $this->renderLayout($this->view->render('forgot-password.phtml'));
    }
}
