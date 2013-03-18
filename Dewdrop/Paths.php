<?php

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
     * @return string The root of the WP install (i.e. where wp-config.php lives)
     */
    public function getWpRoot()
    {
        return $this->wpRoot;
    }

    /**
     * @return string The Dewdrop folder inside "lib"
     */
    public function getDewdropLib()
    {
        return $this->dewdropLib;
    }

    /**
     * @return string The root of your Dewdrop-based plugin
     */
    public function getPluginRoot()
    {
        return $this->pluginRoot;
    }

    /**
     * @return string The folder housing your admin components
     */
    public function getAdmin()
    {
        return $this->pluginRoot . '/admin';
    }

    /**
     * @return string The folder housing your dbdeploy deltas
     */
    public function getDb()
    {
        return $this->pluginRoot . '/db';
    }

    /**
     * @return string The "lib" folder inside your plugin
     */
    public function getLib()
    {
        return $this->pluginRoot . '/lib';
    }

    /**
     * @return string The folder housing your models
     */
    public function getModels()
    {
        return $this->pluginRoot . '/models';
    }

    /**
     * @return string The folder housing your shortcodes
     */
    public function getShortCodes()
    {
        return $this->pluginRoot . '/short-codes';
    }

    /**
     * @return string The folder housing your plugin's tests
     */
    public function getTests()
    {
        return $this->pluginRoot . '/tests';
    }
}
