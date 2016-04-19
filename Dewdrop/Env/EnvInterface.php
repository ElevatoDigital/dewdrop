<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Env;

use Pimple;

/**
 * This is the interface that must be implemented if you'd like to support a new
 * environment for Dewdrop applications.  Add your Env class to \Dewdrop\Env::getAll()
 * to hook into the bootstrap and configuration lifecycle in Dewdrop.
 */
interface EnvInterface
{
    /**
     * Determine if your environment is currently in use.  Typically, you'll look for
     * the existence of some object, constant or class that indicates your environment
     * is being used.
     *
     * @return boolean
     */
    public function isInUse();

    /**
     * Return a string that can be used to describe projects in this environment.  Typically
     * either app or plugin depending upon how Dewdrop is typically used.
     *
     * @return string
     */
    public function getProjectNoun();

    /**
     * Get the configuration data Dewdrop needs to operate.  Should be of this shape:
     *
     * [
     *     'bootstrap' => '\Your\Bootstrap\ClassName',
     *     'db' => [
     *         'type'     => 'pgsql|mysql',
     *         'username' => 'user',
     *         'password' => '123456!',
     *         'name'     => 'my_database',
     *         'host'     => 'localhost'
     *     ]
     * ]
     *
     * @return array
     */
    public function getConfigData($file = null);

    /**
     * Get the bootstrap class that should be used for your environment.  Will often
     * come from the config data.  However, if you want to allow users to override
     * the bootstrap class in your environment via some other constant or configuration
     * flag, you can implement that logic here.
     *
     * @return string
     */
    public function getBootstrapClass();

    /**
     * This method gets called on all environments when Dewdrop is run on the CLI.
     * It isn't only called on the active/in-use environment.  This gives your
     * environment an opportunity to configure any globals or PHP settings it needs
     * to operate well on the CLI.
     *
     * @return void
     */
    public function bootstrapCli();

    /**
     * If, after the bootstrapCli() portion of the execution lifecycle, Dewdrop determines
     * that your environment is the one that is currently in use, this method will be
     * called to allow you to perform any application boot-up needed.
     *
     * @return void
     */
    public function initializeCli();

    /**
     * Add or override any Pimple resources.
     *
     * @param Pimple $pimple
     * @return void
     */
    public function initializePimple(Pimple $pimple);

    /**
     * Provide session and session.storage resources for Pimple.  The "session" resource
     * should be the environment-specific session object typically used (e.g.
     * Zend_Session_Namespace, WP_Session, etc) and "session.storage" should be an
     * implementor of \Dewdrop\Session\SessionStorageInterface that wraps that platform-
     * specific API.
     *
     * @param Pimple $pimple
     * @return void
     */
    public function providePimpleSessionResource(Pimple $pimple);
}
