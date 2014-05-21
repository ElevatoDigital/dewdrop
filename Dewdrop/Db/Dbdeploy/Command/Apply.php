<?php

namespace Dewdrop\Db\Dbdeploy\Command;

use Dewdrop\Db\Dbdeploy\ChangelogGateway;
use Dewdrop\Db\Dbdeploy\CliExec;

class Apply implements CommandInterface
{
    private $changesets;

    private $changelogGateway;

    private $cliExec;

    private $changeset;

    private $changesAppliedCount = 0;

    private $appliedFilesByChangeset = array();

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

    public function getChangesAppliedCount()
    {
        return $this->changesAppliedCount;
    }

    public function getAppliedFilesByChangeset()
    {
        return $this->appliedFilesByChangeset;
    }

    /**
     * Run the specified SQL script through the RDBMS binary.
     *
     * @param string $path
     * @return boolean Whether the mysql command ran successfully.
     */
    protected function runSqlScript($path)
    {
    }
}