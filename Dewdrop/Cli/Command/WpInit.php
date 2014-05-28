<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Exception;

/**
 * A command to handle creation of folders commonly used in WordPress
 * plugins written in Dewdrop.  This command should be run after installing
 * Dewdrop with Composer to get the basic structure for plugin in place.
 */
class WpInit extends CommandAbstract
{
    /**
     * Setup the arguments, examples and aliases for this command.
     */
    public function init()
    {
        $this
            ->setDescription('Create folder structure for WP plugin project')
            ->setCommand('wp-init')
            ->addAlias('wordpress-init')
            ->addAlias('init-wp')
            ->addAlias('init-wordpress');

        $this->addExample(
            'Basic usage to create folders needed for WP plugin development',
            './vendor/bin/dewdrop wp-init'
        );
    }

    /**
     * Create paths commonly used in Dewdrop-based WordPress plugins.
     *
     * @return void
     */
    public function execute()
    {
        $plugin = $this->paths->getPluginRoot();

        if (false === stripos($plugin, 'wp-content/plugins')) {
            throw new Exception('You do not appear to be running Dewdrop in a WP plugin');
        }

        $cwd = getcwd();

        chdir($plugin);

        $paths = array(
            'admin',
            'db',
            'lib',
            'models',
            'models/metadata',
            'tests',
            'www',
        );

        foreach ($paths as $path) {
            if (!$this->folderExists($path)) {
                $this->createFolder($path);
            }
        }

        chdir($cwd);
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
