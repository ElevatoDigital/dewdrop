<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Bootstrap;

use Dewdrop\Config;
use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Driver\Wpdb as WpdbDriver;
use Dewdrop\Paths;
use Pimple;

/**
 * Bootstrap Dewdrop in a WP environment by providing the needed
 * resources, drawing upon information from WP's config.
 *
 * If you need to define a custom bootstrap class for your WP
 * plugin, you can do so by specifying that class in your
 * wp-config.php.  For example:
 *
 * <pre>
 * define('DEWDROP_BOOTSTRAP_CLASS', '\My\Project\Bootstrap');
 * </pre>
 */
class Wp implements PimpleProviderInterface
{
    /**
     * The Pimple DI container used to provide resources to Dewdrop.
     *
     * @var Pimple
     */
    private $pimple;

    /**
     * Define all the needed resources for Dewdrop.
     */
    public function __construct()
    {
        $this->pimple = new Pimple();

        $this
            ->defineConfig()
            ->defineDb();
    }

    public static function handleWpLoadGlobals()
    {
        // So many friggin' dots!
        $wpLoadPath = realpath(__DIR__ . '/../../../../../../../../wp-load.php');

        if (file_exists($wpLoadPath)) {
            global
                $wp,
                $wp_query,
                $wp_the_query,
                $wp_rewrite,
                $wp_did_header,
                $redirection;

            require_once $wpLoadPath;
        }
    }

    /**
     * Hand over the Pimple.
     *
     * @return Pimple
     */
    public function getPimple()
    {
        return $this->pimple;
    }

    /**
     * Define config resource for Pimple.  Nothing much to do for WP.
     *
     * @return Config
     */
    public function defineConfig()
    {
        $this->pimple['config'] = $this->pimple->share(
            function () {
                return new Config();
            }
        );

        return $this;
    }

    /**
     * Define the DB resource for Pimple.
     *
     * @return DbAdapter
     */
    public function defineDb()
    {
        $this->pimple['db'] = $this->pimple->share(
            function () {
                global $wpdb;

                $adapter = new DbAdapter();
                $driver  = new WpdbDriver($adapter, $wpdb);

                $adapter->setDriver($driver);

                return $adapter;
            }
        );

        return $this;
    }
}
