<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

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
    protected $wpRoot;

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
        $this->wpRoot     = realpath(__DIR__ . '/../../../../../');
        $this->dewdropLib = __DIR__;
        $this->pluginRoot = realpath($this->dewdropLib . '/../../');
    }

    /**
     * The root of the WP install (i.e. where wp-config.php lives)
     *
     * @return string
     */
    public function getWpRoot()
    {
        return $this->wpRoot;
    }

    /**
     * The Dewdrop folder inside "lib"
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
     * The folder housing your admin components
     *
     * @return string
     */
    public function getAdmin()
    {
        return $this->pluginRoot . '/admin';
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
}
