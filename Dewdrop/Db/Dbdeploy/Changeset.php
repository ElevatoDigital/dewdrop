<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy;

use GlobIterator;
use Dewdrop\Db\Dbdeploy\Exception\InvalidFilename;

/**
 * This class simplifies interacting with a dbdeploy changeset.
 * Given a changeset name, which is used when logging the applied
 * changes in the database, and a filesystem path, where the SQL
 * scripts can be found, this class can tell you:
 *
 * 1. What revisions have already been applied to the database?
 * 2. What new revisions are available to apply?
 * 3. What are the paths to the available SQL files?
 */
class Changeset
{
    /**
     * The gateway object providing access to the DB's changelog table.
     *
     * @var ChangelogGateway
     */
    private $changelogGateway;

    /**
     * The name of this changeset, as it is stored/tracked in the DB's changelog.
     *
     * @var string
     */
    private $name;

    /**
     * The path to all the dbdeploy scripts for this changeset in the filesystem.
     *
     * @var string
     */
    private $path;

    /**
     * All the files available in this changeset (applied already or not).
     * Populated and "cached" when calling findFilesInPath().
     *
     * @var array
     */
    private $availableFiles = array();

    /**
     * Create a changeset object.
     *
     * @param ChangelogGateway $changelogGateway
     * @param string $name
     * @param string $path
     */
    public function __construct(ChangelogGateway $changelogGateway, $name, $path)
    {
        $this->changelogGateway = $changelogGateway;
        $this->name             = $name;
        $this->path             = $path;
    }

    /**
     * Get the name of this changeset.  Useful from the context of Command
     * objects interacting with multiple changesets.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Check the DB changelog to find the highest revision number that has
     * been applied for this changeset.
     *
     * @return int
     */
    public function getCurrentRevision()
    {
        return $this->changelogGateway->getCurrentRevisionForChangeset($this->name);
    }

    /**
     * Check to see the maximum available revision number for this changeset.
     *
     * @return int
     */
    public function getAvailableRevision()
    {
        $changeNumbers = array_keys($this->findFilesInPath());

        return array_pop($changeNumbers);
    }

    /**
     * Get an array of the files that have already been applied to the
     * databse for this changeset.  Note that the keys of the array are
     * the revision change numbers.
     *
     * @return array
     */
    public function getAppliedFiles()
    {
        $appliedFiles = array();

        foreach ($this->findFilesInPath() as $changeNumber => $file) {
            if ($changeNumber <= $this->getCurrentRevision()) {
                $appliedFiles[$changeNumber] = $file;
            }
        }

        return $appliedFiles;
    }

    /**
     * Get an array of the files that have not yet been applied to the
     * databse for this changeset.  Note that the keys of the array are
     * the revision change numbers.
     *
     * @return array
     */
    public function getNewFiles()
    {
        $newFiles = array();

        foreach ($this->findFilesInPath() as $changeNumber => $file) {
            if ($changeNumber > $this->getCurrentRevision()) {
                $newFiles[$changeNumber] = $file;
            }
        }

        return $newFiles;
    }

    /**
     * Find all valid SQL files in this changeset's path, whether they've
     * been applied already or not.  Note that the array keys are the
     * revision change numbers.
     *
     * @return array
     */
    private function findFilesInPath()
    {
        if (!count($this->availableFiles)) {
            $files = new GlobIterator($this->path . '/*.sql');

            foreach ($files as $file) {
                $changeNumber = $this->getFileChangeNumber($file);

                $this->availableFiles[$changeNumber] = $file;
            }

            ksort($this->availableFiles);
        }

        return $this->availableFiles;
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

        if (!preg_match('/^[0-9]{5}-/', $file)) {
            throw new InvalidFilename(
                "Change file \"$file\" does not follow the dbdeploy naming conventions."
            );
        }

        $changeNumber = (int) substr(
            $file,
            0,
            strpos($file, '-')
        );

        return $changeNumber;
    }
}
