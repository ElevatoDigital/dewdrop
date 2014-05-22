<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy\Command;

use Dewdrop\Db\Dbdeploy\ChangelogGateway;
use Dewdrop\Db\Dbdeploy\Exception;

/**
 * Backfill the dbdeploy changelog without actually running SQL
 * scripts.  This can be useful if your DB schema is out of sync
 * with your changelog table because someone, for example, applied
 * a schema change manually rather than using dbdeploy's Apply
 * command.
 *
 * This command works on a single named changeset and will back
 * fill up to a maximum revision number that you specify.
 */
class Backfill implements CommandInterface
{
    /**
     * The gateway object used to write to the changelog table.
     *
     * @var \Dewdrop\Db\Dbdeploy\ChangelogGateway
     */
    private $changelogGateway;

    /**
     * The array of available changesets.
     *
     * @var array
     */
    private $changesets = array();

    /**
     * The name of the changeset you'd like to backfill in the
     * changelog table (e.g. "plugin").
     *
     * @var string
     */
    private $changesetName;

    /**
     * The maximum revision number for which you'd like to backfill
     * the log.  For example, if you specify a revision of 7 and the
     * log is currently at revision 2 for the given changeset, then
     * revisions 3-7 will be added to the log, leaving revisions 8
     * and greater to be applied later.
     *
     * @var int
     */
    private $revision;

    /**
     * The number of changes that were logged during the previous
     * call to execute().
     *
     * @var int
     */
    private $changesAppliedCount = 0;

    /**
     * An array of the files that were logged during the previous call
     * to execute().
     *
     * @var array
     */
    private $appliedFiles = array();

    /**
     * Note that $changsetName and $revision are required to run the backfill
     * command.
     *
     * @throws \Dewdrop\Db\Dbdeploy\Exception
     * @param ChangelogGateway $changelogGateway
     * @param array $changesets
     * @param string $changesetName
     * @param int $revision
     */
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

    /**
     * Find the changeset to backfill and then backfill the changelog up to
     * maximum revision number.  Note that the start and end times for the
     * log entry will be identical.
     *
     * @throws \Dewdrop\Db\Dbdeploy\Exception
     */
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
            throw new Exception("No changeset matching {$this->changesetName} found.");
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

        return true;
    }

    /**
     * Get the number of changes added to the changelog during the last call to
     * execute().
     *
     * @return int
     */
    public function getChangesAppliedCount()
    {
        return $this->changesAppliedCount;
    }

    /**
     * Get an array of the file names applied during the previous call to
     * execute().
     *
     * @return array
     */
    public function getAppliedFiles()
    {
        return $this->appliedFiles;
    }
}
