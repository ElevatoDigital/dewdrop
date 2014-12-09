<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Db\Adapter as DbAdapter;
use Dewdrop\Exception;
use Dewdrop\Inflector;

/**
 * Refresh all DB metadata definition files.
 */
class DbMetadata extends CommandAbstract
{
    /**
     * Set basic command information, arguments and examples
     *
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
            './vendor/bin/dewdrop db-metadata'
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
        $db   = $this->getDbAdapter();
        $path = $this->getPath();

        if (!file_exists($path)) {
            mkdir($path);
        }

        $tables = $db->listTables();

        $this->writeMetadataFiles($path, $db, $tables);
        $this->deleteMetadataForDroppedTables($path, $tables);
    }

    /**
     * Write metadata files for all the listed tables.
     *
     * @param string $path
     * @param DbAdapter $db
     * @param array $tables
     * @return void
     */
    protected function writeMetadataFiles($path, DbAdapter $db, array $tables)
    {
        $inflector = new Inflector();

        foreach ($tables as $table) {
            $columns           = $db->describeTable($table);
            $references        = $db->listForeignKeyReferences($table);
            $uniqueConstraints = $db->listUniqueConstraints($table);
            $title             = $inflector->titleize($table);

            $replacements = array(
                '{{singular}}'          => $inflector->singularize($title),
                '{{plural}}'            => $inflector->pluralize($title),
                '{{columns}}'           => var_export($columns, true),
                '{{references}}'        => var_export($references, true),
                '{{uniqueConstraints}}' => var_export($uniqueConstraints, true),
            );

            file_put_contents(
                "$path/$table.php",
                str_replace(
                    array_keys($replacements),
                    $replacements,
                    file_get_contents(__DIR__ . '/db-metadata/template.tpl')
                )
            );
        }
    }

    /**
     * Delete metadata files for any tables that have been dropped and no longer
     * exist in the table list.
     *
     * @param string $path
     * @param array $tables
     * @throws \Dewdrop\Exception
     * @return void
     */
    protected function deleteMetadataForDroppedTables($path, array $tables)
    {
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            $tableName = basename($file, '.php');

            if (!in_array($tableName, $tables)) {
                // WARNING: Notice the error suppression "@" operator!  Used because
                //          failure is also reported by unlink() return value.
                if (!@unlink($file)) {
                    throw Exception("Could not delete DB metadata file for \"{$tableName}\"");
                }
            }
        }
    }

    /**
     * @return DbAdapter
     */
    protected function getDbAdapter()
    {
        $db = $this->runner->connectDb();
        return $db;
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return $this->paths->getModels() . '/metadata';
    }
}
