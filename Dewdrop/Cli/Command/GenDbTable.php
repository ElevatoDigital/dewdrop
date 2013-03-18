<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Cli\Command;

/**
 * Generate a model class and a dbdeploy delta for a new database table.
 *
 * @todo Just a stub.  Not yet implemented.
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
     * Generate the model class and dbdeploy delta and then output the path
     * to each so that they can easily be found for editing.
     *
     * @return void
     */
    public function execute()
    {

    }
}
