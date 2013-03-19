<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Cli\Command\DbMetadata;

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
     * The path the mysql binary.  If not specified, we'll attempt to
     * auto-detect it.
     *
     * @var string
     */
    private $mysql;

    /**
     * When running the backfill action, the revision up to which you'd like
     * to backfill your database's changelog.
     *
     * @var integer
     */
    private $revision;

    /**
     * The path where the dbdeploy delta scripts can be found.
     *
     * @var string
     */
    private $scriptPath;

    /**
     * The name of the changeset in the dbdeploy_changelog table.  You can
     * track multiple streams of changes by using differing changeset names.
     *
     * @var string
     */
    private $changeset = 'plugin';

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
            'Which action to execution: status, backfill or update [default]',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'mysql',
            'The path to the mysql binary',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'revision',
            "The revision number you'd like to backfill the changelog to",
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'changeset',
            'The name of the changeset the scripts should be applied to',
            self::ARG_OPTIONAL,
            array('changeset-name')
        );

        $this->addArg(
            'script-path',
            'The folder in which the dbdeploy delta scripts can be found',
            self::ARG_OPTIONAL,
            array('path')
        );

        $this->addExample(
            'Apply all new dbdeploy scripts to your database',
            './dewdrop dbdeploy'
        );

        $this->addExample(
            'Check your database to see if any scripts need to be applied',
            './dewdrop dbdeploy status'
        );

        $this->addExample(
            'Backfill your changelog up to a certain revision number',
            './dewdrop dbdeploy backfill --revision=5'
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
     * Set the path where the dbdeploy script files can be found.  By default,
     * the "db" folder in your plugin's root folder will be used.
     *
     * @param string $scriptPath
     * @return \Dewdrop\Cli\Command\Dbdeploy
     */
    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $scriptPath;

        return $this;
    }

    /**
     * Set the name of the changeset you'd like to you when checking the
     * current revision number and adding new entries to the dbdeploy_changelog
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
     * @return void
     */
    public function execute()
    {
        if (null === $this->action) {
            $this->action = 'update';
        }

        if (!in_array($this->action, $this->validActions)) {
            return $this->abort(
                "\"{$this->action}\" is not a valid action.  Valid actions are: "
                . implode(', ', $this->validActions)
            );
        }

        $this->db = $this->runner->connectDb();

        if (!$this->changelogExists() && !$this->createChangelog()) {
            return $this->abort('Could not create dbdeploy changelog table.');
        }

        $method = 'execute' . ucfirst($this->action);
        $this->$method();
    }

    /**
     * Run any available updates.  If no updates are available, we display
     * status information instead.
     *
     * @return void
     */
    public function executeUpdate()
    {
        $current = $this->getCurrentRevision();
        $files   = $this->getChangeFiles($current);

        // Abort was called because a file was named improperly
        if (false === $files) {
            return false;
        }

        $count = count($files);

        if (!$count) {
            return $this->executeStatus();
        }

        foreach ($files as $file) {
            $start   = date('Y-m-d G:i:s');
            $success = $this->runSqlScript($file);

            if (!$success) {
                $filename = basename($file);

                return $this->abort(
                    "Stopping dbdeploy run because of error in script: {$filename}"
                );
            }

            $end = date('Y-m-d G:i:s');

            $this->updateChangelog($file, $start, $end);
        }

        $suffix  = (1 === $count ? '' : 's');
        $changes = array();

        foreach ($files as $file) {
            $changes[] = basename($file);
        }

        $this->refreshDbMetadata();

        $this->renderer
            ->title('dbdeploy Complete')
            ->success("Successfully applied $count change file{$suffix}.")
            ->newline()
            ->subhead('Change files applied')
            ->unorderedList($changes)
            ->newline();
    }

    /**
     * Display dbdeploy status information including the DB's current
     * revision and any update scripts that need to be run to bring it
     * up to date.
     *
     * @return void
     */
    public function executeStatus()
    {
        $current = $this->getCurrentRevision();
        $files   = $this->getChangeFiles($current);

        // Abort was called because a file was named improperly
        if (false === $files) {
            return false;
        }

        $count = count($files);

        $this->renderer->title('dbdeploy Status');

        if (!$count) {
            $this->renderer->success('Your database schema is up to date.');
        } elseif (1 === $count) {
            $this->renderer->warn("You need to run {$count} dbdeploy script.");
        } else {
            $this->renderer->warn("You need to run {$count} dbdeploy scripts.");
        }

        $this->renderer->newline();

        $this->renderer->text("Change Set: {$this->changeset}");

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

        if ($count) {
            $this->renderer->subhead('Scripts that need to be run');

            $listItems = array();

            foreach ($files as $file) {
                $listItems[] = basename($file);
            }

            $this->renderer
                ->unorderedList($listItems)
                ->newline();
        }
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
     * @return void
     */
    public function executeBackfill()
    {
        if (null === $this->revision) {
            return $this->abort('The revision arg is required for the backfill action.');
        }

        $current = $this->getCurrentRevision();
        $files   = $this->getChangeFiles($current);

        // Abort was called because a file was named improperly
        if (false === $files) {
            return false;
        }

        $count = count($files);

        if (!$count) {
            return $this->executeStatus();
        }

        foreach ($files as $file) {
            $timestamp = date('Y-m-d G:i:s');
            $revision  = $this->getFileChangeNumber($file);

            if ($revision <= $this->revision) {
                $this->updateChangelog($file, $timestamp, $timestamp);
            }
        }

        $suffix  = (1 === $count ? '' : 's');
        $changes = array();

        foreach ($files as $file) {
            $changes[] = basename($file);
        }

        $this->renderer
            ->title('dbdeploy Backfill Complete')
            ->text("Successfully backfilled changelog entries for $count change file{$suffix}.")
            ->newline()
            ->subhead('Changelog entries inserted')
            ->unorderedList($changes)
            ->newline();
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
     * Check to see if the dbdeploy_changelog table already exists.
     *
     * @return boolean
     */
    private function changelogExists()
    {
        return in_array('dbdeploy_changelog', $this->db->listTables());
    }

    /**
     * Create the changelog table by running the SQL script included with
     * Dewdrop.
     *
     * @return boolean Whether it was successfully created.
     */
    private function createChangelog()
    {
        return $this->runSqlScript(__DIR__ . '/dbdeploy/dbdeploy-changelog.sql');
    }

    /**
     * Update the changelog with a record for a newly executed file.
     *
     * @param string $file
     * @param string $startDt
     * @param string $completeDt
     */
    private function updateChangelog($file, $startDt, $completeDt)
    {
        $this->db->insert(
            'dbdeploy_changelog',
            array(
                'change_number' => $this->getFileChangeNumber($file),
                'delta_set'     => $this->changeset,
                'start_dt'      => $startDt,
                'complete_dt'   => $completeDt,
                'applied_by'    => (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown'),
                'description'   => $file
            )
        );
    }

    /**
     * Determine the current DB revision by looking for the maximum
     * change_number value in the dbdeploy_changelog table.
     *
     * @return integer
     */
    private function getCurrentRevision()
    {
        return (int) $this->db->fetchOne(
            'SELECT MAX(change_number) FROM dbdeploy_changelog WHERE delta_set = ?',
            array(
                'delta_set' => $this->changeset
            )
        );
    }

    /**
     * Run the specified SQL script through the mysql binary.
     *
     * @param string $path
     * @return boolean Whether the mysql command ran successfully.
     */
    private function runSqlScript($path)
    {
        if (null === $this->mysql) {
            $this->mysql = $this->autoDetectExecutable('mysql');
        }

        $cmd = sprintf(
            '%s --user=%s --password=%s --host=%s %s < %s 2>&1',
            $this->mysql,
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASSWORD),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_NAME),
            escapeshellarg($path)
        );

        return 0 === $this->passthru($cmd);
    }

    /**
     * Get the files with a change number greater than the current revision.
     *
     * @param integer $currentRevision
     * @return array The files that need to be run.
     */
    private function getChangeFiles($currentRevision)
    {
        $out   = array();
        $path  = $this->evalPathArgument($this->scriptPath ?: $this->paths->getDb());
        $files = glob("{$path}/*.sql");

        foreach ($files as $file) {
            $changeNumber = $this->getFileChangeNumber($file);
            $filename     = basename($file);

            if (!preg_match('/^[0-9]{5}-/', $filename)) {
                return $this->abort("Change file \"$filename\" does not follow the dbdeploy naming conventions.");
            }

            if ($changeNumber > $currentRevision) {
                $out[$changeNumber] = realpath($file);
            }
        }

        ksort($out);

        return $out;
    }

    /**
     * Determine the change number for the provided file name.  Files should be
     * named in this format:
     *
     * 00001-short-description-of-change.sql
     *
     * Where "00001" is the change number padded with zeros to 5 digits in order
     * to ensure future changes sort nicely in a file listing and the change number
     * and any words included in the file name are separated by hyphens.
     *
     * @param string $file
     * @return integer
     */
    private function getFileChangeNumber($file)
    {
        $file = basename($file);

        $changeNumber = (int) substr(
            $file,
            0,
            strpos($file, '-')
        );

        return $changeNumber;
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
}
