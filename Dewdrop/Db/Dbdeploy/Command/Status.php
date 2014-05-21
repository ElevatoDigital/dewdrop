<?php

namespace Dewdrop\Db\Dbdeploy\Command;

use Dewdrop\Db\Dbdeploy\Changeset;

class Status implements CommandInterface
{
    /**
     * @var array
     */
    private $changesets = array();

    private $availableChangesCount = 0;

    private $availableChangesBySet = array();

    public function __construct(array $changesets)
    {
        $this->changesets = $changesets;
    }

    public function execute()
    {
        $this->availableChangesCount = 0;
        $this->availableChangesBySet = array();

        foreach ($this->changesets as $changeset) {
            $current   = $changeset->getCurrentRevision();
            $available = $changeset->getAvailableRevision();

            $this->availableChangesCount += ($available - $current);

            $this->availableChangesBySet[$changeset->getName()] = array(
                'current' => $current,
                'files'   => $changeset->getNewFiles()
            );
        }

        return true;
    }

    public function getAvailableChangesCount()
    {
        return $this->availableChangesCount;
    }

    public function getAvailableChangesBySet()
    {
        return $this->availableChangesBySet;
    }
}