<?php

namespace Dewdrop\Db\Dbdeploy\Command;

use Dewdrop\Db\Dbdeploy\ChangelogGateway;
use Dewdrop\Db\Dbdeploy\Exception;

class Backfill implements CommandInterface
{
    private $changelogGateway;

    private $changesets = array();

    private $changesetName;

    private $revision;

    private $changesAppliedCount = 0;

    private $appliedFiles = array();

    public function __construct(ChangelogGateway $changelogGateway, array $changesets, $changesetName, $revision)
    {
        $this->changelogGateway = $changelogGateway;
        $this->changesets       = $changesets;
        $this->changesetName    = $changesetName;
        $this->revision         = (int) $revision;

        if (!$this->changesetName) {
            throw new Exception('You must specify a changeset name to backfill.');
        }

        if (!$this->revision) {
            throw new Exception('You must specify a revision number to backfill to.');
        }
    }

    public function execute()
    {
        $this->appliedFiles        = array();
        $this->changesAppliedCount = 0;

        $matchingChangeset = null;

        /* @var $changeset \Dewdrop\Db\Dbdeploy\Changeset */
        foreach ($this->changesets as $changeset) {
            if ($changeset->getName() === $this->changesetName) {
                $matchingChangeset = $changeset;
                break;
            }
        }

        if (null === $matchingChangeset) {
            throw new Exception("No changeset matching {$this->chagnesetName} found.");
        }

        foreach ($matchingChangeset->getNewFiles() as $changeNumber => $file) {
            if ($changeNumber <= $this->revision) {
                $this->changelogGateway->logAppliedFile(
                    $matchingChangeset->getName(),
                    $changeNumber,
                    $file,
                    (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown'),
                    date('Y-m-d G:i:s'),
                    date('Y-m-d G:i:s')
                );

                $this->appliedFiles[]       = $file;
                $this->changesAppliedCount += 1;
            }
        }
    }

    public function getChangesAppliedCount()
    {
        return $this->changesAppliedCount;
    }

    public function getAppliedFiles()
    {
        return $this->appliedFiles;
    }
}
