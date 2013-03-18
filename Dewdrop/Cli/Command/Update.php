<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

/**
 * Pull latest Dewdrop code from git.
 *
 * It would be great to have this integrate with a changelog facility so we
 * could let the user know what changes had occurred since their last update.
 */
class Update extends CommandAbstract
{
    /**
     * The path to the git executable.
     *
     * @var string
     */
    protected $git;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Pull the latest Dewdrop library code from Github')
            ->setCommand('update')
            ->addAlias('pull');

        $this->addArg(
            'git',
            'The path to the git executable',
            self::ARG_OPTIONAL
        );
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
        if (null === $this->git) {
            $this->git = $this->autoDetectExecutable('git');
        }

        $cwd = getcwd();

        // Change to lib/ folder so git isn't confused
        chdir($this->paths->getLib());

        $cmd = sprintf(
            '%s pull',
            $this->git
        );

        $this->passthru($cmd);

        chdir($cwd);
    }
}
