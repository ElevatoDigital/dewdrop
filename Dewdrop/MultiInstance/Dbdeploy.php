<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\MultiInstance;

use Dewdrop\Db\Adapter;
use Dewdrop\Db\Dbdeploy\ChangelogGateway;
use Dewdrop\Db\Dbdeploy\Changeset;
use Dewdrop\Db\Dbdeploy\CliExec;
use Dewdrop\Db\Dbdeploy\Command\Apply;
use Dewdrop\Db\Dbdeploy\Command\Backfill;
use Dewdrop\Db\Dbdeploy\Command\Status;
use Dewdrop\Env;
use Dewdrop\Paths;

/**
 * Class Dbdeploy
 *
 * @package Dewdrop\MultiInstance
 */
class Dbdeploy
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $changesetPath;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * Dbdeploy constructor.
     *
     * @param array $config
     * @param Adapter $adapter
     * @param null $changesetPath
     * @throws Exception
     */
    public function __construct(array $config, Adapter $adapter, $changesetPath = null)
    {
        $this->paths         = new Paths();
        $this->config        = $config;
        $this->adapter       = $adapter;
        $this->changesetPath = ($changesetPath ?: $this->detectChangesetPath());
    }

    /**
     * @param $changesetPath
     * @return $this
     */
    public function setChangesetPath($changesetPath)
    {
        $this->changesetPath = $changesetPath;

        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    private function detectChangesetPath()
    {
        $pattern    = $this->paths->getPluginRoot() . '/db-*';
        $candidates = glob($pattern, GLOB_ONLYDIR);

        if (1 === count($candidates)) {
            return current($candidates);
        }

        throw new Exception('Unable to detect changeset path.');
    }

    /**
     * @return CliExec
     */
    private function getCliExec()
    {
        $config = $this->config;

        var_dump($config);

        return new CliExec($config['type'], $config['username'], $config['password'], $config['host'], $config['name']);
    }

    /**
     * @return ChangelogGateway
     */
    private function getChangeLogGateway()
    {
        return new ChangelogGateway($this->adapter, $this->getCliExec(), $this->config['type']);
    }

    /**
     * @return array
     */
    public function getChangesets()
    {
        $mainChangesetName = Env::getInstance()->getProjectNoun();
        $changesets        = [];
        $gateway           = $this->getChangeLogGateway();

        $defaultChangesets = [
            'dewdrop-core'     => $this->paths->getDewdropLib() . '/db/' . $this->config['type'],
            $mainChangesetName => $this->changesetPath,
            'dewdrop-test'     => $this->paths->getDewdropLib() . '/tests/db/' . $this->config['type']
        ];

        foreach ($defaultChangesets as $name => $path) {
            $changesets[] = new Changeset($gateway, $name, $path);
        }

        return $changesets;
    }

    /**
     * @return Apply
     */
    public function update()
    {
        $command = new Apply(
            $this->getChangeLogGateway(),
            $this->getChangesets(),
            $this->getCliExec()
        );

        $command->execute();

        return $command;
    }

    /**
     * @return Status
     */
    public function status()
    {
        $command = new Status($this->getChangesets());

        $command->execute();

        return $command;
    }

    /**
     * @param $changeset
     * @param $revision
     * @return Backfill
     * @throws \Dewdrop\Db\Dbdeploy\Exception
     */
    public function backfill($changeset, $revision)
    {
        $command = new Backfill(
            $this->getChangeLogGateway(),
            $this->getChangesets(),
            $changeset,
            $revision
        );

        $command->execute();

        return $command;
    }
}
