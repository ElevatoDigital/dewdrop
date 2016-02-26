<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Zf1;

use Dewdrop\Bootstrap\Standalone;
use Dewdrop\Config;
use Dewdrop\Db\Adapter;
use Dewdrop\Db\Driver\Pdo\Pgsql as PgsqlDriver;
use Dewdrop\View\View;
use Zend_Db_Table_Abstract;

class Bootstrap extends Standalone
{
    public function init()
    {
        $this->pimple['debug'] = $this->pimple->share(
            function () {
                /* @var $config Config */
                $config = $this->pimple['config'];
                return $config->has('debug') && $config->get('debug');
            }
        );

        $this->pimple['db'] = $this->pimple->share(
            function () {
                /* @var $pdo \PDO */
                $pdo = Zend_Db_Table_Abstract::getDefaultAdapter()->getConnection();

                $adapter = new Adapter();
                $driver  = new PgsqlDriver($adapter, $pdo);

                return $adapter;
            }
        );

        $this->pimple['config'] = $this->pimple->share(
            function () {
                return new Config();
            }
        );

        $this->pimple['view'] = $this->pimple->share(
            function () {
                $view = new View();
                $view
                    ->registerHelper('inputtext', '\Dewdrop\View\Helper\BootstrapInputText')
                    ->registerHelper('select', '\Dewdrop\View\Helper\BootstrapSelect')
                    ->registerHelper('textarea', '\Dewdrop\View\Helper\BootstrapTextarea');
                return $view;
            }
        );
    }
}
