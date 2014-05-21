<?php

namespace Dewdrop\Db\Dbdeploy\Command;

use Dewdrop\Db\Dbdeploy\Changeset;

interface CommandInterface
{
    public function execute();
}