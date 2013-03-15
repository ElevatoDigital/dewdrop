<?php

namespace Dewdrop\Cli\Command;

class Dbdeploy extends CommandAbstract
{
    public function init()
    {
        $this
            ->setDescription('Update database schema using dbdeploy')
            ->setCommand('dbdeploy')
            ->addAlias('db-deploy')
            ->addAlias('db-migrate')
            ->addAlias('db-migrations');
    }

    public function execute()
    {

    }
}
