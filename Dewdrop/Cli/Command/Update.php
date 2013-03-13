<?php

namespace Dewdrop\Cli\Command;

class Update extends CommandAbstract
{
    public function init()
    {
        $this
            ->setDescription('Pull the latest Dewdrop library code from Github')
            ->setCommand('update')
            ->addAlias('pull');
    }

    public function execute()
    {
        $cwd = getcwd();

        // Change to lib/ folder
        chdir(dirname(dirname(dirname(__DIR__))));

        passthru('git pull');

        chdir($cwd);
    }
}
