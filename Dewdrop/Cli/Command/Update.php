<?php

namespace Dewdrop\Cli\Command;

/**
 * Pull latest Dewdrop code from git.
 *
 * It would be great to have this integrate with a changelog facility so we
 * could let the user know what changes had occurred since their last update.
 *
 * @package Dewdrop
 */
class Update extends CommandAbstract
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Pull the latest Dewdrop library code from Github')
            ->setCommand('update')
            ->addAlias('pull');
    }

    /**
     * Pull latest changes from Git.
     *
     * We switch to the lib/ folder before pulling changes because that's where
     * the Dewdrop repo is initial cloned to.
     *
     * @return void
     */
    public function execute()
    {
        $cwd = getcwd();

        // Change to lib/ folder so git isn't confused
        chdir($this->paths->getLib());

        $cmd = sprintf(
            '%s pull',
            $this->autoDetectExecutable('git')
        );

        $this->passthru($cmd);

        chdir($cwd);
    }
}
