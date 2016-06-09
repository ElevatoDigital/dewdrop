<?php

namespace Dewdrop\View\Helper;

class ActivityLogUserInformation extends AbstractHelper
{
    public function direct(array $userInformation)
    {
        $this->view->headScript()->appendFile(
            $this->view->bowerUrl('/dewdrop/www/js/activity-log-user-information.js')
        );

        return $this->partial('activity-log-user-information.phtml', ['userInformation' => $userInformation]);
    }
}
