<?php

namespace Dewdrop\Cli\Command;

class Dbdeploy extends CommandAbstract
{
    private $validActions = array(
        'update',
        'status'
    );

    private $db;

    private $action;

    private $mysql;

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
            'Which action to execution: status or update (default)',
            self::ARG_OPTIONAL
        );

        $this->addArg(
            'mysql',
            'The path to the mysql binary',
            self::ARG_OPTIONAL
        );
    }

    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    public function setMysql($mysql)
    {
        $this->mysql = $mysql;

        return $this;
    }

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

    public function executeUpdate()
    {
        $current = $this->getCurrentRevision();
        $files   = $this->getChangeFiles($current);
        $count   = count($files);

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

        $this->renderer
            ->title('dbdeploy Complete')
            ->text("Successfully applied $count change file{$suffix}.")
            ->newline()
            ->subhead('Change files applied')
            ->unorderedList($changes)
            ->newline();
    }

    public function executeStatus()
    {
        $current = $this->getCurrentRevision();
        $files   = $this->getChangeFiles($current);
        $count   = count($files);

        $this->renderer->title('dbdeploy Status');

        if (!$count) {
            $this->renderer->text('Your database schema is up to date.');
        } elseif (1 === $count) {
            $this->renderer->text("You need to run {$count} dbdeploy script.");
        } else {
            $this->renderer->text("You need to run {$count} dbdeploy scripts.");
        }

        $this->renderer->newline();

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

    private function changelogExists()
    {
        return in_array('dbdeploy_changelog', $this->db->listTables());
    }

    private function createChangelog()
    {
        return $this->runSqlScript(__DIR__ . '/dbdeploy/dbdeploy-changelog.sql');
    }

    private function updateChangelog($file, $startDt, $completeDt)
    {
        $this->db->insert(
            'dbdeploy_changelog',
            array(
                'change_number' => $this->getFileChangeNumber($file),
                'delta_set'     => 'Main',
                'start_dt'      => $startDt,
                'complete_dt'   => $completeDt,
                'applied_by'    => (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown'),
                'description'   => $file
            )
        );
    }

    private function getCurrentRevision()
    {
        return (int) $this->db->fetchOne('SELECT MAX(change_number) FROM dbdeploy_changelog');
    }

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

    private function getChangeFiles($currentRevision)
    {
        $out   = array();
        $path  = $this->paths->getDb();
        $files = glob("{$path}/*.sql");

        foreach ($files as $file) {
            $changeNumber = $this->getFileChangeNumber($file);

            if ($changeNumber > $currentRevision) {
                $out[$changeNumber] = realpath($file);
            }
        }

        ksort($out);

        return $out;
    }

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
}
