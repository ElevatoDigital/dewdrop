<?php

namespace Dewdrop\MultiInstance;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Driver\Pdo\Pgsql as Pgsql;
use PDO;
use Silex\Application;
use Silex\ServiceProviderInterface;

class Manager implements ServiceProviderInterface
{
    /**
     * @var Instance
     */
    private $currentInstance;

    private $manageDbAdapter;

    private $instanceTableName;

    private $instanceUsername;

    private $instancePassword;

    private $instanceHost;

    private $databaseNameTemplate;

    private $idColumn;

    private $subdomainColumn;

    /**
     * @var Application
     */
    private $silex;

    public function __construct(
        array $dbConfig,
        $instanceTableName,
        $databaseNameTemplate,
        $idColumn = null,
        $subdomainColumn = null
    ) {
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

    public function getManageDbAdapter()
    {
        return $this->manageDbAdapter;
    }

    public function setInstanceUsername($instanceUsername)
    {
        $this->instanceUsername = $instanceUsername;

        return $this;
    }

    public function setInstancePassword($instancePassword)
    {
        $this->instancePassword = $instancePassword;

        return $this;
    }

    public function setInstanceHost($instanceHost)
    {
        $this->instanceHost = $instanceHost;

        return $this;
    }

    public function getIdColumn()
    {
        return $this->idColumn;
    }

    public function getSubdomainColumn()
    {
        return $this->subdomainColumn;
    }

    public function getAll()
    {
        $instances = [];
        $query     = sprintf('SELECT * FROM %s', $this->manageDbAdapter->quoteIdentifier($this->instanceTableName));

        foreach ($this->manageDbAdapter->fetchAll($query) as $instanceMetadata) {
            $instances[] = new Instance($this, $instanceMetadata);
        }

        return $instances;
    }

    public function getById($id)
    {
        $query = sprintf(
            'SELECT * FROM %s WHERE %s = ?',
            $this->manageDbAdapter->quoteIdentifier($this->instanceTableName),
            $this->manageDbAdapter->quoteIdentifier($this->idColumn)
        );

        return new Instance($this, $this->manageDbAdapter->fetchRow($query, [$id]));
    }

    public function getBySubdomain($subdomain)
    {
        $query = sprintf(
            'SELECT * FROM %s WHERE LOWER(%s) = ?',
            $this->manageDbAdapter->quoteIdentifier($this->instanceTableName),
            $this->manageDbAdapter->quoteIdentifier($this->subdomainColumn)
        );

        return new Instance($this, $this->manageDbAdapter->fetchRow($query, [$subdomain]));
    }

    public function setCurrent(Instance $instance)
    {
        $this->currentInstance = $instance;

        $this->modifySilexDbConfigForInstance($instance);
        $this->silex['db'] = $instance->getDbAdapter();

        return $this;
    }

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

    public function getDatabaseNameForInstance(Instance $instance)
    {
        return sprintf($this->databaseNameTemplate, $instance->getId());
    }

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

    public function modifySilexDbConfigForInstance(Instance $instance)
    {
        $config = $this->silex['config'];
        $config['db']['name'] = $this->getDatabaseNameForInstance($instance);
        $this->silex['config'] = $config;

        return $this;
    }

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
     * @return int|string Detected id column name.
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
     * @return int|string Detected subdomain column name.
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
}
