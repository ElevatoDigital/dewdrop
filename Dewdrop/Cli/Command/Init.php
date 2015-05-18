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
 * A command to handle creation of folders commonly used in a stand-alone
 * Dewdrop application. This command should be run after installing
 * Dewdrop with Composer to get the basic structure for app in place.
 */
class Init extends CommandAbstract
{

    /**
     * Directory of current Dewdrop application
     *
     * @var string
     */
    protected $directory;

    /**
     * Setup the arguments, examples and aliases for this command.
     */
    public function init()
    {
        $this
            ->setDescription('Create folder structure for project')
            ->setCommand('init');

        $this->addExample(
            'Basic usage to create folders needed for development',
            './vendor/bin/dewdrop init'
        );
    }

    /**
     * Create paths commonly used in Dewdrop applications.
     *
     * @return void
     */
    public function execute()
    {
        $this->setDirectory($this->paths->getAppRoot());

        if ($message = $this->commandShouldExecute($this->directory)) {
            $this->abort($message);
        }

    }

    /**
     * This command should only be executable under certain conditions.
     *
     * @param $appPath path of the current install
     * @return false|string returns double negative for execution, or error message
     */
    protected function commandShouldExecute($dir)
    {
        if (false !== stripos($dir, 'wp-content/plugins')) {
            return 'You appear to be running Dewdrop in a WP plugin. Run command wp-init instead.';
        }

        return false;
    }

    /**
     * Set directory to Dewdrop application directory
     *
     * @param string $dir
     * @return void
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * This wrapper is really only in place so that we can mock it during testing.
     *
     * @param string $path
     * @return boolean
     */
    protected function folderExists($path)
    {
        return file_exists($path);
    }

    /**
     * This wrapper is really only in place so that we can mock it during testing.
     *
     * @param string $path
     * @return void
     */
    protected function createFolder($path)
    {
        mkdir($path);
    }
}
