<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Paths;

trait DatabaseGeneratorTrait
{

    /**
     * Get the revision number that should be used for the dbdeploy file.
     *
     * Returns the number as a zero-padded string, as suggested in the naming
     * conventions (e.g. "00002").
     *
     * @return string
     */
    protected function getDbRevision()
    {
        $path   = (new Paths())->getDb();
        $files  = glob("{$path}/*.sql");
        $latest = 0;

        foreach ($files as $file) {
            $changeNumber = (int) substr(basename($file), 0, strpos($file, '-'));

            if ($changeNumber > $latest) {
                $latest = $changeNumber;
            }
        }

        return sprintf('%05s', $latest + 1);
    }

    /**
     * Check to see if the table exists in the database already.
     *
     * @param $tableName
     * @return bool Whether or not table exists.
     */
    private function tableExists($tableName)
    {
        return in_array($tableName, $this->runner->connectDb()->listTables());
    }
}
