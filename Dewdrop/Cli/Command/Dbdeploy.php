<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Db\Dbdeploy\Changeset;
use Dewdrop\Db\Dbdeploy\ChangelogGateway;
use Dewdrop\Db\Dbdeploy\CliExec;
use Dewdrop\Db\Dbdeploy\Command\Backfill;
use Dewdrop\Db\Dbdeploy\Command\Status;
use Dewdrop\Db\Dbdeploy\Command\Apply;
use Dewdrop\Env;
use Dewdrop\Exception;
use Dewdrop\Pimple;

/**
 * Apply update to your database schema in a controlled and repeatable manner.
 */
class Dbdeploy extends CommandAbstract
{
    /**
     * The valid options for the action arg.
     *
     * @var array
     */
    private $validActions = array(
        'update',
        'status',
        'backfill'
    );

    /**
     * A reference to the CLI runner's DB connection.  Carried around so it's
     * easier to use throughout this command.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $db;

    /**
     * The action that should be run.
     *
     * @var string
     */
    private $action;

    /**
     * The path to the mysql binary.  If not specified, we'll attempt to
     * auto-detect it.
     *
     * @var string
     */
    private $mysql;

    /**
     * The path to the psql binary.  If not specified, we'll attempt to
     * auto-detect it.
     *
     * @var string
     */
    private $psql;

    /**
     * When running the backfill action, the revision up to which you'd like
     * to backfill your database's changelog.
     *
     * @var integer
     */
    private $revision;

    /**
     * The name of the changeset in the changelog table.  You can track multiple
     * streams of changes by using differing changeset names.
     *
     * @var string
     */
    private $changeset;

    /**
     * The changesets that need to be updated when the default dbdeploy command
     * is run.  If you only want to run a single changeset, you can manually
     * set the changeset argument as well.
     *
     * @var array
     */
    private $changesets = array();

    /**
     * The name of the changelog table.  This is not intended to be modified
     * outside the unit testing environment.  Not available as a command
     * argument.
     *
     * @var string
     */
    private $changelogTableName = 'dbdeploy_changelog';


    /**
     * The type of RDBMS being used.  Should be either "pgsql" or "mysql".
     *
     * @var string
     */
    private $dbType;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Update database schema using dbdeploy')
            ->setCommand('dbdeploy')
            ->addAlias('db-deploy')
            ->addAlias('db-migrate')
            ->addAlias('db-migrations');

        $this->addPrimaryArg(
            'action',
            'Which action to execute: status, backfill or update [default]',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'mysql',
            'The path to the mysql binary',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'psql',
            'The path to the psql binary',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'revision',
            "The revision number you'd like to backfill the changelog to",
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'changeset',
            'Only run scripts for the specified changeset instead of all',
            self::ARG_OPTIONAL,
            array('changeset-name')
        );

        $this->addExample(
            'Apply all new dbdeploy scripts to your database',
            './vendor/bin/dewdrop dbdeploy'
        );

        $this->addExample(
            'Check your database to see if any scripts need to be applied',
            './vendor/bin/dewdrop dbdeploy status'
        );

        $this->addExample(
            'Backfill your changelog up to a certain revision number',
            './vendor/bin/dewdrop dbdeploy backfill --revision=5 --changeset=plugin'
        );
    }

    /**
     * Set the action to run (see the $validActions property for a list)
     *
     * @param string $action
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Manually set the path to the mysql binary
     *
     * @param string $mysql
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function setMysql($mysql)
    {
        $this->mysql = $mysql;

        return $this;
    }

    /**
     * Manually set the path to the psql binary
     *
     * @param string $psql
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function setPsql($psql)
    {
        $this->psql = $psql;

        return $this;
    }

    /**
     * When running the backfill action, set the revision number to backfill to.
     *
     * @param integer $revision
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function setRevision($revision)
    {
        $this->revision = (int) $revision;

        return $this;
    }

    /**
     * Set the name of the changeset you'd like to you when checking the
     * current revision number and adding new entries to the changelog
     * table.  This defaults to "plugin", which is the changeset name that
     * should be used for changes originating from scripts in your plugin's
     * DB folder.
     *
     * @param string $changeset
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function setChangeset($changeset)
    {
        $this->changeset = $changeset;

        return $this;
    }

    /**
     * Determine which action the user has selected (update or status), ensure
     * the changelog table is present and then delegate the remainder of the
     * work to the action's own method.
     *
     * @return boolean
     */
    public function execute()
    {
        $this->initChangesets();

        if (null === $this->action) {
            $this->action = 'update';
        }

        if (!in_array($this->action, $this->validActions)) {
            return $this->abort(
                "\"{$this->action}\" is not a valid action.  Valid actions are: "
                . implode(', ', $this->validActions)
            );
        }

        $config = $this->runner->getPimple()['config']['db'];

        $cliExec = new CliExec(
            $config['type'],
            $config['username'],
            $config['password'],
            $config['host'],
            $config['name'],
            ('psql' === $this->dbType ? $this->psql : $this->mysql)
        );

        $this->db = $this->runner->connectDb();

        $gateway    = new ChangelogGateway($this->db, $cliExec, $this->dbType, $this->changelogTableName);
        $changesets = array();

        foreach ($this->changesets as $name => $path) {
            $changesets[] = new Changeset($gateway, $name, $path);
        }

        $method = 'execute' . ucfirst($this->action);
        return $this->$method($changesets, $gateway, $cliExec);
    }

    /**
     * Run any available updates.  If no updates are available, we display
     * status information instead.
     *
     * @param array $changesets
     * @param ChangelogGateway $changelogGateway
     * @param CliExec $cliExec
     * @return boolean
     */
    public function executeUpdate(array $changesets, ChangelogGateway $changelogGateway, CliExec $cliExec)
    {
        $command = new Apply($changelogGateway, $changesets, $cliExec, $this->changeset);

        $command->execute();

        if (!$command->getChangesAppliedCount()) {
            return $this->executeStatus($changesets);
        }

        $suffix  = (1 === $command->getChangesAppliedCount() ? '' : 's');

        $this->refreshDbMetadata();

        $this->renderer
            ->title('dbdeploy Complete')
            ->success("Successfully applied {$command->getChangesAppliedCount()} change file{$suffix}.")
            ->newline();

        foreach ($command->getAppliedFilesByChangeset() as $changeset => $changes) {
            $this->renderFileList(
                "Change files applied to \"{$changeset}\" changeset",
                $changes,
                'subhead'
            );
        }

        return true;
    }

    /**
     * Display dbdeploy status information including the DB's current
     * revision and any update scripts that need to be run to bring it
     * up to date.
     *
     * @param array $changesets
     * @return boolean
     */
    public function executeStatus(array $changesets)
    {
        $command = new Status($changesets);

        $command->execute();

        $this->renderer->title('dbdeploy Status');

        if (!$command->getAvailableChangesCount()) {
            $this->renderer->success('Your database schema is up to date.');
        } elseif (1 === $command->getAvailableChangesCount()) {
            $this->renderer->warn("You need to run {$command->getAvailableChangesCount()} dbdeploy script.");
        } else {
            $this->renderer->warn("You need to run {$command->getAvailableChangesCount()} dbdeploy scripts.");
        }

        foreach ($command->getAvailableChangesBySet() as $changeset => $changes) {
            $this->renderStatusForChangeset($changeset, $changes['current'], $changes['files']);
        }

        return true;
    }

    /**
     * Render the status for a single changeset, displaying the current and
     * the available revision numbers and a list of any files that need to
     * be run.
     *
     * @param string $changeset
     * @param integer $current
     * @param array $files
     * @return void
     */
    private function renderStatusForChangeset($changeset, $current, array $files)
    {
        $count = count($files);

        $this->renderer
            ->newline()
            ->subhead("{$changeset} Changeset");

        $this->renderer->text(
            sprintf(
                'Current Revision: %05s',
                $current
            )
        );

        $this->renderer->text(
            sprintf(
                'Available Revision: %05s',
                (0 === $count ? $current : array_pop(array_keys($files)))
            )
        );

        $this->renderer->newline();

        $this->renderFileList(
            "Scripts that need to be run in \"{$changeset}\" changeset",
            $files,
            'text'
        );
    }

    /**
     * Fill in changelog entries up to the specified revision number.
     *
     * The backfill action can sometimes be useful if you schema was updated
     * outside dbdeploy or the changelog is inaccurate for any other reason.
     * It will add entries to the changelog, effectively telling future runs
     * of dbdeploy to skip those scripts and move on to those you know still
     * need to be applied to your database.
     *
     * @param array $changesets
     * @param ChangelogGateway $changelogGateway
     * @return boolean
     */
    public function executeBackfill(array $changesets, ChangelogGateway $changelogGateway)
    {
        $command = new Backfill($changelogGateway, $changesets, $this->changeset, $this->revision);

        $command->execute();

        $changesApplied = $command->getChangesAppliedCount();

        if (!$changesApplied) {
            return $this->executeStatus($changesets);
        }

        $suffix  = (1 === $changesApplied ? '' : 's');

        $this->renderer
            ->title('dbdeploy Backfill Complete')
            ->text("Successfully backfilled changelog entries for {$changesApplied} change file{$suffix}.")
            ->newline();

        $this->renderFileList('Changelog entries inserted', $command->getAppliedFiles(), 'subhead');

        return true;
    }

    /**
     * Extend the CommandAbstract help display with information on dbdeploy
     * naming conventions.
     *
     * @return void
     */
    public function help()
    {
        parent::help();

        // Naming conventions
        $this->renderer
            ->subhead('Naming conventions for dbdeploy files')
            ->text('Files should be named in this format:')
            ->newline()
            ->text('    00001-short-description-of-change.sql')
            ->newline()
            ->text(
                'Where "00001" is the change number padded with zeros to 5 digits in order '
                . 'to ensure future changes sort nicely in a file listing, and the change number '
                . 'and any words included in the file name are separated by hyphens.'
            )
            ->newline();
    }

    /**
     * Run command to refresh DB metadata following successful schema updates.
     *
     * @return void
     */
    private function refreshDbMetadata()
    {
        $command = new DbMetadata($this->runner, $this->renderer);
        $command->execute();
    }

    /**
     * Render a file list with the supplied subhead, assuming there is at least
     * one file to include in the list.
     *
     * @param string $header
     * @param array $files
     * @param string $rendererMethod
     * @return void
     */
    private function renderFileList($header, array $files, $rendererMethod)
    {
        if (!count($files)) {
            return;
        }

        if ('subhead' === $rendererMethod) {
            $this->renderer->subhead($header);
        } elseif ('text' === $rendererMethod) {
            $this->renderer
                ->text("### {$header}")
                ->newline();
        } else {
            throw new Exception('Only "subhead" and "text" can be used for rendererMethod');
        }

        $listItems = array();

        foreach ($files as $file) {
            $listItems[] = basename($file);
        }

        $this->renderer
            ->unorderedList($listItems)
            ->newline();
    }

    /**
     * Setup the default changesets.  If a changeset has already been configured with a given
     * name, the default will not be applied.  This is done primarily to allow the
     * overrideChangesetPath() method to swap out the default paths during testing.
     *
     * @return void
     */
    private function initChangesets()
    {
        $this->dbType = $this->runner->getPimple()['config']['db']['type'];

        $mainChangesetName = Env::getInstance()->getProjectNoun();

        $defaultChangesets = [
            $mainChangesetName => $this->paths->getPluginRoot() . '/db',
            'dewdrop-core'     => $this->paths->getDewdropLib() . '/db/' . $this->dbType,
            'dewdrop-test'     => $this->paths->getDewdropLib() . '/tests/db/' . $this->dbType
        ];

        if (Pimple::hasResource('dbdeploy.changesets')) {
            $defaultChangesets = array_merge($defaultChangesets, Pimple::getResource('dbdeploy.changesets'));
        }

        foreach ($defaultChangesets as $name => $path) {
            if (!array_key_exists($name, $this->changesets)) {
                $this->changesets[$name] = $path;
            }
        }
    }

    /**
     * Override the default changelog table name.
     *
     * This is really only intended for testing purposes.  You'll notice that
     * it's not availble as an argument on this command and that it's using
     * abnormal syntax (i.e. "override" instead of "set").  We don't anticipate
     * or encourage people using alternative changelog table names for their
     * production code.
     *
     * @param string $tableName
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function overrideChangelogTableName($tableName)
    {
        $this->changelogTableName = $tableName;

        return $this;
    }

    /**
     * Override the path for a changeset.
     *
     * The new path value should be supplied relative to the plugin root folder.
     * This isn't really intended to be used outside out of the testing
     * environment.
     *
     * @param string $changeset
     * @param string $path
     * @throws \Dewdrop\Exception
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function overrideChangesetPath($changeset, $path)
    {
        $this->changesets[$changeset] = $path;

        return $this;
    }
}
