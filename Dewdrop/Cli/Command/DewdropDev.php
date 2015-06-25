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
 * This command makes it easy to replace your Composer-based Dewdrop install
 * with a clone of dev-master from git.  This allows you to commit changes to
 * Dewdrop itself without leaving the context of your project.  Obviously,
 * you can branch away from dev-master, create tags, etc. in the newly created
 * clone.  Once you complete your edits, you can remove the clone and run a
 * Composer update to put the release copy of Dewdrop back in place.
 */
class DewdropDev extends CommandAbstract
{
    /**
     * The path to the git binary.  If not specified, we'll attempt to
     * auto-detect it.
     *
     * @var string
     */
    private $git;

    /**
     * The URL to the Dewdrop git repo that should be cloned for development.
     *
     * @var string
     */
    private $dewdropUrl = 'https://github.com/DeltaSystems/dewdrop.git';

    /**
     * Configure the command metadata and help information.
     *
     * @return void
     */
    public function init()
    {
        $this
            ->setDescription('Replace the Composer-installed Dewdrop with dev-master from git')
            ->setCommand('dewdrop-dev');

        $this->addArg(
            'git',
            'The path to the git binary',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'dewdrop-url',
            'The URL to the Dewdrop git repository',
            self::ARG_OPTIONAL
        );

        $this->addExample(
            'Replace a Composer-installed Dewdrop with a git checkout to modify Dewdrop itself',
            './vendor/bin/dewdrop dewdrop-dev'
        );
    }

    /**
     * Manually override the location of the git binary that should be used for
     * the checkout.
     *
     * @param string $git
     * @return \Dewdrop\Cli\Command\DewdropDev
     */
    public function setGit($git)
    {
        $this->git = $git;

        return $this;
    }

    /**
     * Manually override the location of the Dewdrop git repo that should be
     * cloned.
     *
     * @param string $dewdropUrl
     * @return \Dewdrop\Cli\Command\DewdropDev
     */
    public function setDewdropUrl($dewdropUrl)
    {
        $this->dewdropUrl = $dewdropUrl;

        return $this;
    }

    /**
     * Replace the current Dewdrop installation with a git clone.  If the install
     * is already a git clone, we'll abort to avoid messing up any edits.  We
     * also move the Composer-based install to dewdrop.composer.bak instead of
     * deleting it in case the developer made edits before running this command.
     *
     * @retur void
     */
    public function execute()
    {
        if (null === $this->git) {
            $this->git = $this->autoDetectExecutable('git');
        }

        $cwd  = getcwd();
        $path = 'vendor/deltasystems/dewdrop/';

        chdir($this->paths->getPluginRoot());

        // Bail if it's already a git clone
        if ($this->gitCloneAlreadyPresent($path)) {
            return $this->abort('Your Dewdrop install is already a git clone');
        }

        if (!file_exists($path) || !is_dir($path)) {
            // Bail if we can't find Dewdrop
            return $this->abort('Could not find Dewdrop installation');
        } else {
            // Otherwise, move the Composer-based install
            $this->moveDewdropInstallation($path);
        }

        $cmd = sprintf(
            '%s clone %s %s 2>&1',
            $this->git,
            escapeshellarg($this->dewdropUrl),
            escapeshellarg($path)
        );

        // Initializing exit status to failed state
        $exitStatus = 1;

        // Create the actual git clone
        $this->exec($cmd, $output, $exitStatus);

        chdir($cwd);

        // Show git output, if it failed for any reason
        if ($exitStatus) {
            return $this->abort(
                is_array($output) ? implode(PHP_EOL, $output) : 'Could not clone git repo'
            );
        }
    }

    /**
     * This method is only in place so we can mock it during testing.
     *
     * @param string $path The path to the Dewdrop install.
     * @return void
     */
    protected function moveDewdropInstallation($path)
    {
        rename($path, dirname($path) . '/dewdrop.composer.bak');
    }

    /**
     * This method is only in place so we can mock it during testing.
     *
     * @param string $path The path to the Dewdrop install.
     * @return boolean
     */
    protected function gitCloneAlreadyPresent($path)
    {
        return file_exists($path . '/.git');
    }
}
