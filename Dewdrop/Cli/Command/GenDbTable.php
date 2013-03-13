<?php

namespace Dewdrop\Cli\Command;

class GenDbTable extends CommandAbstract
{
    private $name;

    private $modelClass;

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

    public function execute()
    {

    }
}
