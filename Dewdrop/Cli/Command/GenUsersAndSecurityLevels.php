<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Db\Driver\Pdo\Pgsql;
use Dewdrop\Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class GenUsersAndSecurityLevels extends GenAdminComponent
{
    use DatabaseGeneratorTrait;

    const TABLE_NAME_USERS = 'users';

    const TABLE_NAME_SECURITY_LEVELS = 'security_levels';

    public function init()
    {
        $this
            ->setDescription('Generate models for Users and Security Levels, generate component for users, '.
                'create tables for users and security_levels if they do not already exist. (WP is not yet supported)')
            ->setCommand('gen-users-and-security-levels')
            ->addAlias('generate-users-and-security-levels');
    }

    public function execute()
    {
        $this
            ->haltIfPrerequisitesNotMet()
            ->createDbTables()
            ->createModels()
            ->createAdmin();
    }

    /**
     * Define prerequisites for this command.
     *
     * @return $this
     * @throws Exception
     */
    private function haltIfPrerequisitesNotMet()
    {
        /* Only Pgsql driver is supported. */
        $driver = $this->runner->connectDb()->getDriver();
        if (!$driver instanceof Pgsql) {
            throw new Exception('This command does not yet support the database driver: '.get_class($driver));
        }

        return $this;
    }

    /**
     * Create db tables for Users and Security Levels if they do not already exist.
     *
     * @return $this
     * @throws Exception
     */
    private function createDbTables()
    {
        $driver = $this->runner->connectDb()->getDriver();

        if ($driver instanceof Pgsql) {
            $dbPrefix = 'pg';
        } else {
            throw new Exception('This command does not yet support the database driver: '.get_class($driver));
        }

        // TableName => filename
        $tables = [
            self::TABLE_NAME_SECURITY_LEVELS => $dbPrefix.'-create-security-levels.sql',
            self::TABLE_NAME_USERS           => $dbPrefix.'-create-users.sql'
        ];

        foreach ($tables as $tableName => $filename) {
            $this->createMigration($tableName, $filename);
        }

        $this->runner->executeCommand('Dbdeploy');

        return $this;
    }

    /**
     * Create a database migration, first checking if a given $tableName exists.
     *
     * @param $tableName
     * @param $filename
     */
    private function createMigration($tableName, $filename)
    {
        if (!$this->tableExists($tableName)) {
            $path     = $this->paths->getDb().'/'.$this->getDbRevision().'-'.$filename;
            $contents = file_get_contents(__DIR__."/gen-templates/users-and-security-levels/db/{$filename}");

            $this->writeFile($path, $contents);
        }
    }

    /**
     * Create Model files from their templates.
     *
     * @return $this
     */
    protected function createModels()
    {
        $source      = __DIR__.'/gen-templates/users-and-security-levels/models';
        $destination = $this->paths->getModels();

        $this->copyFilesAndFillTemplates($source, $destination);

        return $this;
    }

    /**
     * Create admin files from their templates.
     *
     * @return $this
     */
    protected function createAdmin()
    {
        $source      = __DIR__.'/gen-templates/users-and-security-levels/admin';
        $destination = $this->paths->getAdmin();

        $this->copyFilesAndFillTemplates($source, $destination);

        return $this;
    }

    /**
     * For a given $source directory, replace template values with a given set of $replacements,
     * and save them to the $destination directory (not altering any existing files that may
     * already exist in the $destination)
     *
     * @param $source
     * @param $destination
     * @param array $replacements
     * @return $this
     */
    protected function copyFilesAndFillTemplates($source, $destination, $replacements = [])
    {
        if (is_dir($destination)) {
            $this->createFolder($destination);
        }

        $source      = realpath($source);
        $destination = realpath($destination);
        $dirIterator = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $templates   = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($templates as $template) {
            // replace source with destination
            $newLocation = $destination.'/'.substr($template->getRealPath(), strlen($source) + 1);

            if ($template->isDir()) {
                $this->createFolder($template->getRealPath());
            } else {
                $this->writeFileFromTemplate($newLocation, $template, $replacements, false);
            }
        }

        return $this;
    }
}
