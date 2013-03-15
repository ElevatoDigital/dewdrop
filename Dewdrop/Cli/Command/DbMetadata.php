<?php

namespace Dewdrop\Cli\Command;

/**
 * Refresh all DB metadata definition files.
 *
 * @package Dewdrop
 */
class DbMetadata extends CommandAbstract
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Generate DB metadata files for each table')
            ->setCommand('db-metadata')
            ->addAlias('dbmetadata')
            ->addAlias('db-meta')
            ->addAlias('dbmeta')
            ->addAlias('update-db-metadata');

        $this->addExample(
            'Basic usage to update metadata for all tables in your DB',
            './dewdrop db-metadata'
        );
    }

    /**
     * Iterate over the list of DB tables and populate a metadata file for
     * each one.
     *
     * @return void
     */
    public function execute()
    {
        $db   = $this->runner->connectDb();
        $path = $this->paths->getModels() . '/metadata';

        if (!file_exists($path)) {
            mkdir($path);
        }

        $tables = $db->listTables();

        foreach ($tables as $table) {
            $metadata = $db->describeTable($table);

            file_put_contents(
                "$path/$table.php",
                "<?php\nreturn " . var_export($metadata, true) . ";\n"
            );
        }
    }
}
