<?php

namespace Dewdrop;

class Paths
{
    protected $wpRoot;

    protected $dewdropLib;

    protected $pluginRoot;

    public function __construct()
    {
        $this->wpRoot     = realpath(__DIR__ . '/../../../../../');
        $this->dewdropLib = __DIR__;
        $this->pluginRoot = realpath($this->dewdropLib . '/../../');
    }

    public function getWpRoot()
    {
        return $this->wpRoot;
    }

    public function getDewdropLib()
    {
        return $this->dewdropLib;
    }

    public function getPluginRoot()
    {
        return $this->pluginRoot;
    }

    public function getAdmin()
    {
        return $this->pluginRoot . '/admin';
    }

    public function getDb()
    {
        return $this->pluginRoot . '/db';
    }

    public function getLib()
    {
        return $this->pluginRoot . '/lib';
    }

    public function getModels()
    {
        return $this->pluginRoot . '/models';
    }

    public function getShortCodes()
    {
        return $this->pluginRoot . '/short-codes';
    }

    public function getTests()
    {
        return $this->pluginRoot . '/tests';
    }
}
