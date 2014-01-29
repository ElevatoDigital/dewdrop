<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

use Dewdrop\Inflector;

/**
 * Generate a set of tables for storing EAV attributes and values.  We create
 * a single table for storing attribute definintions and a table for each
 * supported "backend" or data type (i.e. varchar, text, decimal, int, and
 * datetime).
 *
 * To use this command, you must specify the database table name you'd like
 * the EAV tables to link to:
 *
 * <pre>
 * ./vendor/bin/dewdrop gen-eav --table-name widgets
 * </pre>
 */
class GenEav extends CommandAbstract
{
    /**
     * The name of the database table you'd like to attach EAV attributes
     * and values to.  Essentially, the database table you name here serves
     * as the "entity" portion of the Entity Attribute Value setup.
     *
     * @var string
     */
    private $tableName;

    /**
     * Reference to the DB adapter (used to load metadata for the entity table
     * so we can determine its primary key).
     *
     * @var \Dewdrop\Db\Adapter
     */
    private $db;

    /**
     * The primary key columns of the entity table as defined in the table's
     * metadata file.
     *
     * @var array
     */
    private $entityPrimaryKey;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Create a set of EAV tables attached to an existing database table')
            ->setCommand('gen-eav')
            ->addAlias('generate-eav')
            ->addAlias('generate-eav-tables');

        $this->addPrimaryArg(
            'table-name',
            'The database table name',
            self::ARG_REQUIRED
        );

        $this->addExample(
            'Generate EAV attribute and value tables attached to your "staff" entity table',
            './vendor/bin/dewdrop gen-eav staff'
        );
    }

    /**
     * Set the name of the DB table you'd like to create EAV attribute and
     * value tables for.
     *
     * @param string $tableName
     * @return \Dewdrop\Cli\Command\GenEav
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Generate the model class and dbdeploy delta and then output the path
     * to each so that they can easily be found for editing.
     *
     * @return void
     */
    public function execute()
    {
        $this->db = $this->runner->connectDb();

        $inflector    = new Inflector();
        $dbdeployFile = $this->paths->getDb() . '/' . $this->getDbRevision() . '-add-' . $this->tableName . '-eav.sql';

        if ($this->dbdeployFileAlreadyExists($dbdeployFile)) {
            return $this->abort("There is already a dbdeploy file at \"{$dbdeployFile}\"");
        }

        $templateReplacements = array(
            '{{tableName}}'                    => $this->tableName,
            '{{primaryKey}}'                   => $inflector->singularize($this->tableName) . '_id',
            '{{primaryKeyColumns}}'            => $this->generatePkeyColumnContent(),
            '{{multiColumnPrimaryKeyIndexes}}' => $this->generateMultiColumnPkeyIndexContent(),
            '{{primaryKeyForeignKeys}}'        => $this->generatePkeyForeignKeyContent(),
            '{{primaryKeyColumnList}}'         => $this->generatePkeyColumnListContent()
        );

        $this->writeFile(
            $dbdeployFile,
            str_replace(
                array_keys($templateReplacements),
                $templateReplacements,
                file_get_contents(__DIR__ . '/gen-templates/eav/eav-tables.sql')
            )
        );

        $this->renderSuccessMessage($dbdeployFile, $modelFile);
    }

    /**
     * Render a success message that lets the user know where their newly
     * generated file is located.
     *
     * @param string $dbdeployFile
     * @return void
     */
    protected function renderSuccessMessage($dbdeployFile)
    {
        $base = $this->paths->getWpRoot();

        $files = array(
            'dbdeploy' => str_replace($base, '', $dbdeployFile),
        );

        $this->renderer
            ->title('gen-db-table')
            ->success('The dbdeploy file for your new EAV tables has been generated')
            ->newline()
            ->subhead('File Locations')
            ->table($files)
            ->subhead('Next Steps')
            ->text(
                'If you do not have any custom attribute columns to add, you can run '
                . 'dbdeploy now to add the tables to your database.'
            )
            ->newline();
    }

    /**
     * Write a file at the specified path with the supplied contents.
     *
     * This is a separate method so that it's easy to mock during testing.
     *
     * @param string $path
     * @param string $contents
     * @return \Dewdrop\Cli\Command\GenAdminComponent
     */
    protected function writeFile($path, $contents)
    {
        file_put_contents($path, $contents);

        return $this;
    }

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
        $path   = $this->paths->getDb();
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
     * Determine if the specified dbdeploy file already exists.
     *
     * Really only around for mocking during tests.
     *
     * @param string $file
     * @return param boolean
     */
    protected function dbdeployFileAlreadyExists($file)
    {
        return file_exists($file);
    }

    /**
     * Generate the content for the {{primaryKeyColumns}} template placeholder.
     * This placeholder is used to add columns to each EAV value table that
     * correspond to each column in the root table's primary key.
     *
     * @return string
     */
    protected function generatePkeyColumnContent()
    {
        $pkey  = $this->getEntityPrimaryKey();
        $lines = array();

        foreach ($pkey as $name => $column) {
            $type = strtoupper($column['DATA_TYPE']);

            $lines[] = "    {$name} {$type} NOT NULL,";
        }

        return trim(implode(PHP_EOL, $lines));
    }

    /**
     * Generate the content for the {{multiColumnPrimaryKeyIndexes}} tempalte
     * placeholder.  When the root table has more than one column in its primary
     * key, it's important that we explicitly add an index for all columns except
     * the first because in a multi-column key, only the first column can use the
     * key itself as an index.  Adding these additional indexes, then, ensure we
     * still have speedy value lookups when the root table has a multi-column
     * primary key.
     *
     * @return string
     */
    protected function generateMultiColumnPkeyIndexContent()
    {
        $pkey = $this->getEntityPrimaryKey();

        if (1 >= count($pkey)) {
            return '';
        }

        $pkey = array_slice($pkey, 1);
        $out  = array();

        foreach ($pkey as $name => $column) {
            $out[] = "    INDEX({$name}),";
        }

        return PHP_EOL . implode(PHP_EOL, $out) . PHP_EOL;
    }

    /**
     * Generate the content for the {{primaryKeyForeignKeys}} template
     * placeholder.  This placeholder is used to define foreign keys pointing
     * back from each EAV value table to the root table.
     *
     * @return string
     */
    protected function generatePkeyForeignKeyContent()
    {
        $pkey = $this->getEntityPrimaryKey();

        foreach ($pkey as $name => $column) {
            $out[] = "    FOREIGN KEY ({$name}) REFERENCES {$this->tableName} ({$name}),";
        }

        return trim(implode(PHP_EOL, $out));
    }

    /**
     * Generate the content for the {{primaryKeyColumnList}} tempalte
     * placeholder.  This placeholder is used to create a comma-separated list
     * of columns corresponding the primary key columns from the root table
     * for each in each EAV value table's primary key definition.
     *
     * @return string
     */
    protected function generatePkeyColumnListContent()
    {
        $pkey = $this->getEntityPrimaryKey();
        $out  = array();

        foreach ($pkey as $name => $column) {
            $out[] = $name;
        }

        return implode(', ', $out);
    }

    /**
     * Get an array representing the columns in the root table's primary
     * key.  The returned array's keys will be the column names and the values
     * will be the metadata for that column as returned by
     * \Dewdrop\Db\Adapter::describeTable().
     *
     * @return array
     */
    protected function getEntityPrimaryKey()
    {
        if (!$this->entityPrimaryKey) {
            $meta = $this->db->getTableMetadata($this->tableName);
            $pkey = array();

            foreach ($meta['columns'] as $name => $column) {
                if ($column['PRIMARY']) {
                    $pkey[$name] = $column;
                }
            }

            $this->entityPrimaryKey = $pkey;
        }

        return $this->entityPrimaryKey;
    }
}
