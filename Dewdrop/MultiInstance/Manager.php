<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\MultiInstance;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Driver\Pdo\Pgsql as Pgsql;
use Dewdrop\Pimple;
use PDO;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class Manager
 * This class can be used for many database related functions, primarily detecting the default database,
 * as well as instance creation and management.
 * Example use cases:
 *
 * Defining db in your Bootstrap.php
 * $this->application['db'] = $this->application->share(
 *     function() {
 *         $manager = new Manager($configForManageDb, 'offices', 'office_%d');
 *
 *         return $manager->getCurrent();
 *     }
 * );
 *
 * Creating a new up-to-date app instance
 * $manager->createInstance(4, 'some_subdomain');
 *
 * @package Dewdrop\MultiInstance
 */
class Manager implements ServiceProviderInterface
{
    /**
     * @var Instance
     */
    private $currentInstance;

    /**
     * @var DbAdapter
     */
    private $manageDbAdapter;

    /**
     * @var array
     */
    private $manageDbConfig;

    /**
     * @var string
     */
    private $instanceTableName;

    /**
     * @var string
     */
    private $instanceUsername;

    /**
     * @var string
     */
    private $instancePassword;

    /**
     * @var string
     */
    private $instanceHost;

    /**
     * @var string
     */
    private $databaseNameTemplate;

    /**
     * @var string
     */
    private $idColumn;

    /**
     * @var string
     */
    private $subdomainColumn;

    /**
     * @var Application
     */
    private $silex;

    /**
     * Manager constructor.
     *
     * @param array $dbConfig Array of username, password, host, name, type
     * @param string $instanceTableName Name of table that keeps application instance records. eg. offices
     * @param string $databaseNameTemplate Sprintf format containing exactly 1 specifier for the id column. eg. gt_office_%d
     * @param null $idColumn Manually specify primary key column of the instance table.
     * @param null $subdomainColumn Manually specify subdomain column of the instance table.
     * @throws Exception
     * @throws \Dewdrop\Exception
     */
    public function __construct(
        array $dbConfig,
        $instanceTableName,
        $databaseNameTemplate,
        $idColumn = null,
        $subdomainColumn = null
    ) {
        $this->silex           = Pimple::getResource('admin');
        $this->manageDbConfig  = $dbConfig;
        $this->manageDbAdapter = $this->createAdapterFromConfig($dbConfig);

        $this->instanceTableName = $instanceTableName;
        $this->instanceUsername  = $dbConfig['username'];
        $this->instancePassword  = $dbConfig['password'];
        $this->instanceHost      = $dbConfig['host'];

        $this->databaseNameTemplate = $databaseNameTemplate;
        $this->idColumn             = ($idColumn ?: $this->detectIdColumn());
        $this->subdomainColumn      = ($subdomainColumn ?: $this->detectSubdomainColumn());
    }

    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $this->silex = $app;

        $app['instance-manager'] = $this;
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }

    /**
     * @return DbAdapter
     */
    public function getManageDbAdapter()
    {
        return $this->manageDbAdapter;
    }

    /**
     * @return array
     */
    public function getManageDbConfig()
    {
        return $this->manageDbConfig;
    }

    /**
     * @param $instanceUsername
     * @return $this
     */
    public function setInstanceUsername($instanceUsername)
    {
        $this->instanceUsername = $instanceUsername;

        return $this;
    }

    /**
     * @param $instancePassword
     * @return $this
     */
    public function setInstancePassword($instancePassword)
    {
        $this->instancePassword = $instancePassword;

        return $this;
    }

    /**
     * @param $instanceHost
     * @return $this
     */
    public function setInstanceHost($instanceHost)
    {
        $this->instanceHost = $instanceHost;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdColumn()
    {
        return $this->idColumn;
    }

    /**
     * @return string
     */
    public function getSubdomainColumn()
    {
        return $this->subdomainColumn;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $instances = [];
        $query     = sprintf('SELECT * FROM %s', $this->manageDbAdapter->quoteIdentifier($this->instanceTableName));

        foreach ($this->manageDbAdapter->fetchAll($query) as $instanceMetadata) {
            $instances[] = new Instance($this, $instanceMetadata);
        }

        return $instances;
    }

    /**
     * @param $id
     * @return array
     */
    public function fetchInstanceMetadataById($id)
    {
        $query = sprintf(
            'SELECT * FROM %s WHERE %s = ?',
            $this->manageDbAdapter->quoteIdentifier($this->instanceTableName),
            $this->manageDbAdapter->quoteIdentifier($this->idColumn)
        );

        return $this->manageDbAdapter->fetchRow($query, [$id]);
    }

    /**
     * @param string $subdomain
     * @return array
     */
    public function fetchInstanceMetadataBySubdomain($subdomain)
    {
        $query = sprintf(
            'SELECT * FROM %s WHERE LOWER(%s) = ?',
            $this->manageDbAdapter->quoteIdentifier($this->instanceTableName),
            $this->manageDbAdapter->quoteIdentifier($this->subdomainColumn)
        );

        return $this->manageDbAdapter->fetchRow($query, [$subdomain]);
    }

    /**
     * @param $id
     * @return Instance
     * @throws Exception
     */
    public function getById($id): Instance
    {
        $row = $this->fetchInstanceMetadataById($id);

        if (empty($row)) {
            throw new Exception("An instance with id {$id} does not exist.");
        }

        return new Instance($this, $row);
    }

    /**
     * @param string $subdomain
     * @return Instance
     */
    public function getBySubdomain($subdomain): Instance
    {
        $row = $this->fetchInstanceMetadataBySubdomain($subdomain);

        return new Instance($this, $row);
    }

    /**
     * @param Instance $instance
     * @return $this
     */
    public function setCurrent(Instance $instance)
    {
        $this->currentInstance = $instance;

        $this->modifySilexDbConfigForInstance($instance);
        $this->silex['db'] = $instance->getDbAdapter();

        return $this;
    }

    /**
     * @return Instance
     * @throws Exception
     */
    public function getCurrent()
    {
        if (!$this->currentInstance) {
            if ('cli' === php_sapi_name()) {
                if (!getenv('SUBDOMAIN')) {
                    throw new Exception('Must pass subdomain as an environment variable when running CLI commands.');
                }

                // @todo Use mockable method for testing
                $subdomain = getenv('SUBDOMAIN');
            } else {
                // @todo Use mockable method for testing
                $subdomain = strtolower(substr($_SERVER['SERVER_NAME'], 0, strpos($_SERVER['SERVER_NAME'], '.')));
            }

            $this->setCurrent($this->getBySubdomain($subdomain));
        }

        return $this->currentInstance;
    }

    /**
     * @param Instance $instance
     * @return string
     * @throws Exception
     */
    public function getDatabaseNameForInstance(Instance $instance)
    {
        return $this->getDatabaseNameForId($instance->getId());
    }

    /**
     * @param $id
     * @return string
     */
    public function getDatabaseNameForId($id)
    {
        return sprintf($this->databaseNameTemplate, $id);
    }

    /**
     * @param Instance $instance
     * @return DbAdapter
     * @throws Exception
     */
    public function createAdapterForInstance(Instance $instance)
    {
        $pdo = new PDO(
            sprintf(
                'pgsql:dbname=%s;host=%s',
                $this->getDatabaseNameForInstance($instance),
                $this->instanceHost
            ),
            $this->instanceUsername,
            $this->instancePassword
        );

        $adapter = new DbAdapter();

        new Pgsql($adapter, $pdo);

        return $adapter;
    }

    /**
     * @param Instance $instance
     * @return $this
     * @throws Exception
     */
    public function modifySilexDbConfigForInstance(Instance $instance)
    {
        $config = $this->silex['config'];
        $config['db']['name'] = $this->getDatabaseNameForInstance($instance);
        $this->silex['config'] = $config;

        return $this;
    }

    /**
     * @param array $config
     * @return DbAdapter
     */
    private function createAdapterFromConfig(array $config)
    {
        // @todo Validate config

        $pdo = new PDO(
            'pgsql:dbname=' . $config['name'] . ';host=' . $config['host'],
            $config['username'],
            $config['password']
        );

        $adapter = new DbAdapter();

        new Pgsql($adapter, $pdo);

        return $adapter;
    }

    /**
     * @return string Detected id column name.
     * @throws Exception
     * @throws \Dewdrop\Exception
     */
    private function detectIdColumn()
    {
        $metadata = $this->getManageDbAdapter()->getTableMetadata($this->instanceTableName);

        foreach ($metadata['columns'] as $column => $meta) {
            if ($meta['PRIMARY']) {
                return $column;
            }
        }

        throw new Exception('Unable to detect id column.');
    }

    /**
     * @return string Detected subdomain column name.
     * @throws Exception
     * @throws \Dewdrop\Exception
     */
    private function detectSubdomainColumn()
    {
        $metadata   = $this->getManageDbAdapter()->getTableMetadata($this->instanceTableName);
        $candidates = ['domain', 'subdomain'];

        foreach ($metadata['columns'] as $column => $meta) {
            if (in_array($column, $candidates)) {
                return $column;
            }
        }

        throw new Exception('Unable to detect id column.');
    }

    /**
     * @param $databaseName
     * @return mixed
     */
    private function databaseExists($databaseName)
    {
        $exists = $this->getManageDbAdapter()->fetchRow(
            "SELECT EXISTS(SELECT 1 FROM pg_database WHERE datname = ?)",
            [$databaseName]
        );

        return $exists['exists'];
    }

    /**
     * Create an instance, the schema should have all db migrations.
     *
     * @param $id
     * @param string $subdomain
     * @param bool $copyUsers
     * @return Instance
     * @throws Exception
     */
    public function createInstance($id, $subdomain, $copyUsers = true)
    {
        $databaseName = $this->getDatabaseNameForId($id);

        if ($this->databaseExists($databaseName)) {
            throw new Exception("Database {$databaseName} already exists.");
        }

        $this->getManageDbAdapter()->query("CREATE DATABASE $databaseName");

        $instance = $this->getById($id);

        $instance->dbdeploy()->update();

        if ($copyUsers) {
            $users = $this->getManageDbAdapter()->fetchAll('SELECT * FROM users WHERE deleted = false');

            $this->copyUsersToInstance($users, $instance);
        }

        return $instance;
    }

    /**
     * Copy users from the Manage application to an instance.
     *
     * @param $users
     * @param Instance $instance
     */
    public function copyUsersToInstance($users, Instance $instance)
    {
        foreach ($users as $user) {
            $instance->getDbAdapter()->insert(
                'users',
                [
                    'security_level_id' => 1,
                    'username'          => $user['username'],
                    'first_name'        => $user['first_name'],
                    'last_name'         => $user['last_name'],
                    'password_hash'     => $user['password_hash'],
                    'email_address'     => $user['email_address']
                ]
            );
        }
    }
}
