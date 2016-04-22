<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Exception;

/**
 * This utility makes it easier to navigate the WP environment by supplying
 * quick access to common Dewdrop paths.  Being out in the middle of the plugins
 * folder can make it hard to find your way around, so rather than manually
 * traversing back up the filesystem with a bunch of "__DIR__ . '/../../../../../'"
 * messiness, just use the methods in this class to find your way around.
 */
class Paths
{
    /**
     * The root of the WordPress installation (i.e. the folder containing
     * wp-config.php).
     *
     * @var string
     */
    protected $root;

    /**
     * The location of the Dewdrop libraries.
     *
     * @var string
     */
    protected $dewdropLib;

    /**
     * The root folder of your plugin.
     *
     * @var string
     */
    protected $pluginRoot;

    /**
     * Find the 3 core paths:
     *
     * - The WP root
     * - The lib folder
     * - The plugin root
     */
    public function __construct()
    {
        $this->root       = $this->detectRoot();
        $this->pluginRoot = $this->detectPluginRoot();
        $this->dewdropLib = realpath(__DIR__ . '/../');
    }

    /**
     * The root of the WP install (i.e. where wp-config.php lives)
     *
     * @throws \Dewdrop\Exception
     * @return string
     */
    public function getRoot()
    {
        if (null === $this->root) {
            throw new Exception('Not running in WordPress');
        }

        return $this->root;
    }

    /**
     * Check to see if we're running in WP.  We currently just look for
     * the WPINC constant, but there might be better ways of detecting this.
     *
     * @deprecated
     * @return boolean
     */
    public function isWp()
    {
        return defined('WPINC');
    }

    /**
     * This is just an alias to getRoot().  Used by some older Dewdrop code.
     *
     * @deprecated
     * @return string
     */
    public function getWpRoot()
    {
        return $this->getRoot();
    }

    /**
     * The Dewdrop folder inside "vendor"
     *
     * @return string
     */
    public function getDewdropLib()
    {
        return $this->dewdropLib;
    }

    /**
     * The root of your Dewdrop-based plugin
     *
     * @return string
     */
    public function getPluginRoot()
    {
        return $this->pluginRoot;
    }

    /**
     * Just an alias for getPluginRoot() that can be called when not using
     * Dewdrop in the context of a WordPress plugin.
     *
     * @return string
     */
    public function getAppRoot()
    {
        return $this->getPluginRoot();
    }

    /**
     * We expect a "www" folder inside the plugin/app root where client-side
     * assets can be included.
     *
     * @return string
     */
    public function getWww()
    {
        return $this->getPluginRoot() . '/www';
    }

    /**
     * Get the folder where we can find custom activity log handlers.
     *
     * @return string
     */
    public function getActivityLog()
    {
        return $this->getPluginRoot() . '/activity-log';
    }

    /**
     * The folder housing your admin components
     *
     * @return string
     */
    public function getAdmin()
    {
        return $this->pluginRoot . '/admin';
    }

    /**
     * The folder where custom CLI commands are located.
     *
     * @return string
     */
    public function getCommands()
    {
        return $this->pluginRoot . '/commands';
    }

    /**
     * Get the data folder where Dewdrop can write data on the server.
     * 
     * @return string
     */
    public function getData()
    {
        return $this->pluginRoot . '/data';
    }

    /**
     * The folder housing your dbdeploy deltas
     *
     * @return string
     */
    public function getDb()
    {
        return $this->pluginRoot . '/db';
    }

    /**
     * The "lib" folder inside your plugin
     *
     * @return string
     */
    public function getLib()
    {
        return $this->pluginRoot . '/lib';
    }

    /**
     * The "vendor" folder inside your plugin.
     *
     * @return string
     */
    public function getVendor()
    {
        return $this->pluginRoot . '/vendor';
    }

    /**
     * The folder housing your models
     *
     * @return string
     */
    public function getModels()
    {
        return $this->pluginRoot . '/models';
    }

    /**
     * The folder housing your shortcodes
     *
     * @return string
     */
    public function getShortCodes()
    {
        return $this->pluginRoot . '/short-codes';
    }

    /**
     * The folder housing your plugin's tests
     *
     * @return string
     */
    public function getTests()
    {
        return $this->pluginRoot . '/tests';
    }

    /**
     * Detect the root path for this application.  This is either the
     * main working directory for a standalone application, or the WordPress
     * root folder in WP plugin -- the folder containing wp-config.php.
     *
     * @return string
     */
    protected function detectRoot()
    {
        if (!$this->isWp()) {
            // Running as a stand-alone app
            $out = $this->detectPluginRoot();
        } else {
            // Running inside a WordPress plugin
            $out = substr(
                __DIR__,
                0,
                strpos(__DIR__, 'wp-content/plugins')
            );
        }

        return rtrim($out, '/');
    }

    /**
     * Detect the plugin or app root folder.  In a standalone app, we traverse up
     * the filesystem until we exit the Composer-created vendor folder.  Using this
     * approach, rather than just grabbing the current working directory, allows
     * Dewdrop to work consistently regardless of whether the application is all
     * running inside the document root or with only a front controller in the
     * document root and the remainder of the code stored elsewhere.  In a
     * WordPress app, it is the folder housing the plugin based on Dewdrop.
     *
     * @return string
     */
    protected function detectPluginRoot()
    {
        $out = realpath(__DIR__ . '/../../../../');

        return rtrim($out, '/');
    }
}
