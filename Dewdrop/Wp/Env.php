<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Wp;

use ArrayObject;
use Dewdrop\Bootstrap\Wp as WpBootstrap;
use Dewdrop\Env\EnvInterface;
use Dewdrop\Paths;
use Dewdrop\Wp\Session\Access as SessionAccess;
use Pimple;
use WP_Session;

class Env implements EnvInterface
{
    private $configData = [];

    public function isInUse()
    {
        return defined('WPINC');
    }

    public function getConfigData($file = null)
    {
        if (!$this->configData) {
            $className = '\Dewdrop\Bootstrap\Wp';

            if (defined('DEWDROP_BOOTSTRAP_CLASS')) {
                $className = DEWDROP_BOOTSTRAP_CLASS;
            }

            $this->configData = array(
                'bootstrap' => $className,
                'db' => array(
                    'username' => DB_USER,
                    'password' => DB_PASSWORD,
                    'host'     => DB_HOST,
                    'name'     => DB_NAME,
                    'type'     => 'mysql'
                )
            );
        }

        return $this->configData;
    }

    public function getBootstrapClass()
    {
        return $this->configData['bootstrap'];
    }

    public function bootstrapCli()
    {
        WpBootstrap::handleWpLoadGlobals();
    }

    public function initializeCli()
    {
        $paths      = new Paths();
        $folder     = basename($paths->getPluginRoot());
        $pluginFile = $paths->getPluginRoot() . '/' . $folder . '.php';

        if (file_exists($pluginFile)) {
            require_once $pluginFile;
        }
    }

    public function initializePimple(Pimple $pimple)
    {
    }

    public function providePimpleSessionResource(Pimple $pimple)
    {
        $pimple['session'] = $pimple->share(
            function () {
                if (class_exists('WP_Session')) {
                    /**
                     * WP Session Manager updated to version 3.0 on 2018-01-16.  The new major version
                     * breaks any code that used WP_Session.  Whereas before WP_Session::get_instance()
                     * returned an instance of WP_Session, it now returns $_SESSION.  Basically, their
                     * implementation now just implements a session save handler rather than providing
                     * an API for writing to session storage itself.  So now we just return $_SESSION
                     * here.  We could still call WP_Session::get_instance() but that triggers deprecation
                     * warnings.
                     */
                    return $_SESSION;
                } else {
                    return new ArrayObject();
                }
            }
        );

        $pimple['session.access'] = $pimple->share(
            function () use ($pimple) {
                return new SessionAccess($pimple['session']);
            }
        );
    }

    public function getCurrentUserId()
    {
        $id = get_current_user_id();
        return (0 < $id ? $id : null);
    }

    public function getProjectNoun()
    {
        return 'plugin';
    }
}
