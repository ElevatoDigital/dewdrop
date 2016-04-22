<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Db\Dbdeploy;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Db\Dbdeploy\Exception;
use Dewdrop\Env;

/**
 * This class enables other dbdeploy classes to access the dbdeploy
 * changelog database table without having to depend upon direct
 * access to the database.  This makes it easier to mock and test
 * the other classes in the dbdeploy module by isolating the database
 * to a single location.
 */
class ChangelogGateway
{
    /**
     * The database adapter used to read and write to the database.
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $dbAdapter;

    /**
     * The database type we're interacting with.  Currently has to be
     * either "pgsql" or "mysql".
     *
     * @var string
     */
    private $dbType;

    /**
     * An object used to run SQL scripts through the psql or mysql CLI
     * tools.
     *
     * @var CliExec
     */
    private $cliExec;

    /**
     * The name of the changelog table in the database.  Mostly only
     * changed for testing purposes.
     *
     * @var string
     */
    private $tableName;

    /**
     * The changelog gateway using a DB adapter and a CliExec object to
     * interact with the database.  It will use either psql or mysql
     * depending upon the $dbType you supply.  For testing, or if you
     * really need a different changelog table name in your application,
     * you can optionally change the table name as well.
     *
     * @param DbAdapter $dbAdapter
     * @param CliExec $cliExec
     * @param string $dbType Either "pgsql" or "mysql"
     * @param string $tableName
     */
    public function __construct(DbAdapter $dbAdapter, CliExec $cliExec, $dbType, $tableName = 'dbdeploy_changelog')
    {
        if ('psql' === $dbType) {
            $dbType = 'pgsql';
        }

        $this->dbAdapter = $dbAdapter;
        $this->cliExec   = $cliExec;
        $this->dbType    = $dbType;
        $this->tableName = $tableName;
    }

    /**
     * Get the highest revision number applied for the given changeset name.
     *
     * @param string $changesetName
     * @return int
     */
    public function getCurrentRevisionForChangeset($changesetName)
    {
        if (!$this->tableExists()) {
            $this->createTable();
        }

        return (int) $this->dbAdapter->fetchOne(
            sprintf(
                'SELECT MAX(change_number) FROM %s WHERE delta_set = ?',
                $this->dbAdapter->quoteIdentifier($this->tableName)
            ),
            array($changesetName)
        );
    }

    /**
     * Log the execution of a SQL script to the changelog table.  The
     * $startTime and $endTime params should be supplied in ISO format
     * (i.e. yyyy-mm-dd hh:mm:ss).  The $file param should be the full
     * path to the file, not just the base file name.
     *
     * @param string $changesetName
     * @param int $number
     * @param string $file
     * @param string $appliedBy
     * @param string $startTime
     * @param string $endTime
     * @return int
     */
    public function logAppliedFile($changesetName, $number, $file, $appliedBy, $startTime, $endTime)
    {
        if (!$this->tableExists()) {
            $this->createTable();
        }

        $this->maintainBackwardCompatibilityOnPrimaryChangeset();

        return $this->dbAdapter->insert(
            $this->tableName,
            array(
                'delta_set'     => $changesetName,
                'change_number' => $number,
                'description'   => $file,
                'applied_by'    => $appliedBy,
                'start_dt'      => $startTime,
                'complete_dt'   => $endTime
            )
        );
    }

    /**
     * When Dewdrop was WP-only, the primary dbdeploy changeset was called "plugin".
     * We now support several environments and calling projects "plugins" doesn't
     * make sense in some cases.  To reflect this, the EnvInterface->getProjectNoun()
     * method was added.  Because existing projects had "plugin" in the dbdeploy_changelog
     * already, this method is in place to update any of those records on older
     * projects.
     */
    public function maintainBackwardCompatibilityOnPrimaryChangeset()
    {
        $primaryChangesetName = Env::getInstance()->getProjectNoun();

        if ('plugin' !== $primaryChangesetName) {
            $this->dbAdapter->update(
                $this->tableName,
                ['delta_set' => $primaryChangesetName],
                "delta_set = 'plugin'"
            );
        }
    }

    /**
     * Check to see if the changelog table exists in the database already.
     *
     * @return bool
     */
    protected function tableExists()
    {
        return in_array($this->tableName, $this->dbAdapter->listTables());
    }

    /**
     * Create the changelog table in the database.  The SQL script used will
     * vary for MySQL and Postgres, though only slightly to account for InnoDB
     * use, etc.
     *
     * @return bool
     * @throws Exception
     */
    protected function createTable()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'dewdrop.db.dbdeploy.');
        $template = __DIR__ . '/changelog-sql/' . $this->dbType . '/dbdeploy-changelog.sql';

        $content = str_replace(
            '{{table_name}}',
            $this->tableName,
            file_get_contents($template)
        );

        if (!file_exists($tempFile) || !is_writable($tempFile)) {
            throw new Exception('Could not write to temporary file for dbdeploy changelog.');
        }

        file_put_contents($tempFile, $content, LOCK_EX);

        $result = $this->cliExec->run($tempFile);

        // WARNING: Notice the error suppression "@" operator!  Used because
        // failure is also reported by unlink() return value.
        if (!@unlink($tempFile)) {
            throw new Exception('Could not delete temporary dbdeploy changelog file.');
        }

        return $result;
    }
}
