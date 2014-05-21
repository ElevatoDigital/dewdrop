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

class ChangelogGateway
{
    /**
     * @var \Dewdrop\Db\Adapter
     */
    private $dbAdapter;

    private $dbType;

    private $cliExec;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param DbAdapter $dbAdapter
     */
    public function __construct(DbAdapter $dbAdapter, CliExec $cliExec, $dbType, $tableName = 'dbdeploy_changelog')
    {
        $this->dbAdapter = $dbAdapter;
        $this->cliExec   = $cliExec;
        $this->dbType    = $dbType;
        $this->tableName = $tableName;

        if (!$this->tableExists()) {
            $this->createTable();
        }
    }

    /**
     * Get the highest revision number applied for the given changeset name.
     *
     * @param string $changesetName
     * @return int
     */
    public function getCurrentRevisionForChangeset($changesetName)
    {
        return (int) $this->dbAdapter->fetchOne(
            sprintf(
                'SELECT MAX(change_number) FROM %s WHERE delta_set = ?',
                $this->dbAdapter->quoteIdentifier($this->tableName)
            ),
            array($changesetName)
        );
    }

    public function logAppliedFile($changesetName, $number, $file, $appliedBy, $startTime, $endTime)
    {
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

    protected function tableExists()
    {
        return in_array($this->tableName, $this->dbAdapter->listTables());
    }

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
