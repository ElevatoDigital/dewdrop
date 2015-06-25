<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy\Command;

/**
 * Check the status of the supplied changesets, letting you know what
 * revisions are currently applied in the database along with what
 * revisions are available but not yet applied.
 */
class Status implements CommandInterface
{
    /**
     * The changesets whose status you'd like to check.
     *
     * @var array
     */
    private $changesets = array();

    /**
     * The number of available changes found during the previous call to execute.
     *
     * @var int
     */
    private $availableChangesCount = 0;

    /**
     * An array of available change files and current revisions, grouped by
     * changeset.
     *
     * @var array
     */
    private $availableChangesBySet = array();

    /**
     * Provide an array of changesets to check when calling execute().
     *
     * @param array $changesets
     */
    public function __construct(array $changesets)
    {
        $this->changesets = $changesets;
    }

    /**
     * Iterate overall all available changesets and check their status.
     * You can call getAvailableChangesCount() and getAvailableChangesBySet()
     * after execute() to get status information.
     *
     * @return bool
     */
    public function execute()
    {
        $this->availableChangesCount = 0;
        $this->availableChangesBySet = array();

        /* @var $changeset \Dewdrop\Db\Dbdeploy\Changeset */
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

    /**
     * Get the number of available changes following a call to execute().
     *
     * @return int
     */
    public function getAvailableChangesCount()
    {
        return $this->availableChangesCount;
    }

    /**
     * Get an array of status information following a call to execute.  Will
     * follow this format:
     *
     * <pre>
     * array(
     *     'changeset-1' => array(
     *         'current' => 8,
     *         'files'   => array(
     *             9  => '00009-new-revision.sql',
     *             10 => '00010-new-revision.sql'
     *         )
     *     ),
     *     'changeset-2' => array(
     *         'current' => 8,
     *         'files'   => array()
     *     ),
     * )
     * </pre>
     *
     * @return array
     */
    public function getAvailableChangesBySet()
    {
        return $this->availableChangesBySet;
    }
}
