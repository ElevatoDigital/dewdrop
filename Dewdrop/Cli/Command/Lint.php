<?php

namespace Dewdrop\Cli\Command;

class Lint extends CommandAbstract
{
    public function init()
    {
        $this
            ->setDescription('Check the syntax of all PHP files')
            ->setCommand('lint')
            ->addAlias('check-php');
    }

    public function execute()
    {

    }
}
