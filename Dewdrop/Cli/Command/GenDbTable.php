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
 * Generate a model class and a dbdeploy delta for a new database table.
 */
class GenDbTable extends CommandAbstract
{
    /**
     * The name of the database table you'd like to create.
     *
     * @var string
     */
    private $name;

    /**
     * The class name of the model you'd like to create, if it cannot be
     * accurately inflected from the table name.
     *
     * @var string
     */
    private $modelClass;

    /**
     * Set basic command information, arguments and examples
     *
     * @inheritdoc
     */
    public function init()
    {
        $this
            ->setDescription('Create a dbdeploy delta for a new table and its model class')
            ->setCommand('gen-db-table')
            ->addAlias('db-table')
            ->addAlias('generate-db-table');

        $this->addPrimaryArg(
            'name',
            'The database table name',
            self::ARG_REQUIRED
        );

        $this->addArg(
            'model-class',
            'The class name for the new model associated with this table',
            self::ARG_OPTIONAL
        );

        $this->addExample(
            'Add a new table named "fruits" and auto-detect the model class name',
            './dewdrop gen-db-table fruits'
        );
    }

    /**
     * Set the name of the DB table you'd like to create.
     *
     * @param string $name
     * @return \Dewdrop\Cli\Command\GenDbTable
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Manually set the name of the model class that will be generated, if you'd
     * prefer not to use the name automatically inflected by from the table name.
     *
     * @param string $modelClass
     * @return \Dewdrop\Cli\Command\GenDbTable
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;

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
        $inflector = new Inflector();

        if (null === $this->modelClass) {
            $this->modelClass = $inflector->classify($this->name);
        }

        $modelFile    = $this->paths->getModels() . '/' . $this->modelClass . '.php';
        $dbdeployFile = $this->paths->getDb() . '/' . $this->getDbRevision() . '-add-' . $this->name . '.sql';

        if ($this->modelAlreadyExists($modelFile)) {
            return $this->abort("There is a already a model file named \"{$this->modelClass}.php\"");
        }

        if ($this->dbdeployFileAlreadyExists($dbdeployFile)) {
            return $this->abort("There is already a dbdeploy file at \"{$dbdeployFile}\"");
        }

        $templateReplacements = array(
            '{{modelClass}}' => $this->modelClass,
            '{{tableName}}'  => $this->name
        );

        $this->writeFile(
            $modelFile,
            str_replace(
                array_keys($templateReplacements),
                $templateReplacements,
                file_get_contents(__DIR__ . '/gen-templates/db-table/ModelClass.tpl')
            )
        );

        $templateReplacements = array(
            '{{tableName}}'  => $this->name,
            '{{primaryKey}}' => $inflector->singularize($this->name) . '_id'
        );

        $this->writeFile(
            $dbdeployFile,
            str_replace(
                array_keys($templateReplacements),
                $templateReplacements,
                file_get_contents(__DIR__ . '/gen-templates/db-table/dbdeploy-delta.sql')
            )
        );
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
        $current = (int) $this->runner->connectDb()->fetchOne(
            "SELECT MAX(change_number) FROM dbdeploy_changelog WHERE delta_set = 'plugin'"
        );

        return sprintf(
            '%05s',
            $current + 1
        );
    }

    /**
     * Determine if the specified model file already exists.
     *
     * Really only around for mocking during tests.
     *
     * @param string $file
     * @return param boolean
     */
    protected function modelAlreadyExists($file)
    {
        return file_exists($file);
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
}
