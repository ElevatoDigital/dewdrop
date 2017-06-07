<?php

namespace Dewdrop\View\Helper;

class ActivityLogUserInformation extends AbstractHelper
{
    public function direct(array $userInformation)
    {
        return $this->partial('activity-log-user-information.phtml', ['userInformation' => $userInformation]);
    }
}
