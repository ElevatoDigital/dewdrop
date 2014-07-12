<?php

namespace Dewdrop\Auth\Page;

class ForgotPassword extends PageAbstract
{
    public function respond()
    {
        return $this->renderLayout($this->view->render('forgot-password.phtml'));
    }
}
