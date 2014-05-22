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
use Dewdrop\Db\Dbdeploy\CliExec;

/**
 * This command actually applies the available changes to your
 * database.  You can optionally limit the command to a single
 * changeset.  After applying each change, it will be logged
 * to the changelog in the database.  If any script fails,
 * execution will be halted immediately.
 */
class Apply implements CommandInterface
{
    /**
     * The changesets for which changes should be applied.
     *
     * @var array
     */
    private $changesets;

    /**
     * The gateway that will be used to write changes to the database.
     *
     * @var \Dewdrop\Db\Dbdeploy\ChangelogGateway
     */
    private $changelogGateway;

    /**
     * An object used to run SQL scripts on the command-line.
     *
     * @var \Dewdrop\Db\Dbdeploy\CliExec
     */
    private $cliExec;

    /**
     * An optional changeset name.  If specific, _only_ changes from the
     * changeset with a matching name will be applied.
     *
     * @var string
     */
    private $changeset;

    /**
     * The number of changes that were applied during execution.  Re-calculated
     * on each call to execute().  Useful for summarizing the changes after
     * execution.
     *
     * @var int
     */
    private $changesAppliedCount = 0;

    /**
     * The specific changes that were applied during execution.  Re-calculated
     * on each call to execute().  Useful for summarizing the changes after
     * execution.  Changes are grouped by changeset name.
     *
     * @var array
     */
    private $appliedFilesByChangeset = array();

    /**
     * The $changelogGateway and $cliExec params allow the command to interact
     * with the database.  The $changeset param is optional.  If specified, it
     * will limit the applied changes to a single named changeset.
     *
     * @param ChangelogGateway $changelogGateway
     * @param array $changesets
     * @param CliExec $cliExec
     * @param null $changeset
     */
    public function __construct(
        ChangelogGateway $changelogGateway,
        array $changesets,
        CliExec $cliExec,
        $changeset = null
    ) {
        $this->changelogGateway = $changelogGateway;
        $this->changesets       = $changesets;
        $this->cliExec          = $cliExec;
        $this->changeset        = $changeset;
    }

    /**
     * Apply changes to the database.  After execution, you can get a count
     * of the applied changes and an array of the change files applied.
     *
     * @return bool
     */
    public function execute()
    {
        $this->changesAppliedCount     = 0;
        $this->appliedFilesByChangeset = array();

        /** @var $changeset \Dewdrop\Db\Dbdeploy\Changeset */
        foreach ($this->changesets as $changeset) {
            $this->appliedFilesByChangeset[$changeset->getName()] = array();

            if (null === $this->changeset || $changeset->getName() === $this->changeset) {
                foreach ($changeset->getNewFiles() as $changeNumber => $file) {
                    $startTime = date('Y-m-d G:i:s');

                    $this->cliExec->run($file);

                    $this->changesAppliedCount += 1;
                    $this->appliedFilesByChangeset[$changeset->getName()][] = $file;

                    $this->changelogGateway->logAppliedFile(
                        $changeset->getName(),
                        $changeNumber,
                        $file,
                        (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown'),
                        $startTime,
                        date('Y-m-d G:i:s')
                    );
                }
            }
        }

        return true;
    }

    /**
     * Get a count of the changes that were applied when execute() was last called.
     *
     * @return int
     */
    public function getChangesAppliedCount()
    {
        return $this->changesAppliedCount;
    }

    /**
     * Get an array of the change files applied during the previous call to
     * execute(), grouped by the changeset name.
     *
     * @return array
     */
    public function getAppliedFilesByChangeset()
    {
        return $this->appliedFilesByChangeset;
    }
}
