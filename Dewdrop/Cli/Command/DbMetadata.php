<?php

namespace Dewdrop\Cli\Command;

use Dewdrop\Inflector;

/**
 * Refresh all DB metadata definition files.
 *
 * @category   Dewdrop
 * @package    Cli
 * @subpackage Command
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

        $tables    = $db->listTables();
        $inflector = new Inflector();

        foreach ($tables as $table) {
            $columns = $db->describeTable($table);
            $title   = $inflector->titleize($table);

            $replacements = array(
                '{{singular}}' => $inflector->singularize($title),
                '{{plural}}'   => $inflector->pluralize($title),
                '{{columns}}'  => var_export($columns, true)
            );

            file_put_contents(
                "$path/$table.php",
                str_replace(
                    array_keys($replacements),
                    $replacements,
                    file_get_contents(__DIR__ . '/db-metadata/template.php')
                )
            );
        }
    }
}
