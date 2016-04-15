<?php

namespace Dewdrop\ActivityLog\Handler;

use Dewdrop\ActivityLog;
use Dewdrop\Db\Table;
use Dewdrop\Inflector;
use Dewdrop\Pimple;

class TableHandler extends CrudHandlerAbstract
{
    /**
     * @var Table
     */
    private $table;

    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @var callable
     */
    private $renderTitleTextCallback;

    public function __construct(Table $table, ActivityLog $activityLog = null, Inflector $inflector = null)
    {
        $this->table     = $table;
        $this->inflector = ($inflector ?: Pimple::getResource('inflector'));

        $tableName     = $table->getTableName();
        $inflectedName = $this->inflector->singularize($this->inflector->hyphenize($tableName));

        $this
            ->setActivityLog(($activityLog ?: Pimple::getResource('activity-log')))
            ->setModel($table)
            ->setName($inflectedName)
            ->addAlias($tableName);

        parent::__construct();
    }

    public function init()
    {

    }

    public function setRenderTitleTextCallback(callable $renderTitleTextCallback)
    {
        $this->renderTitleTextCallback = $renderTitleTextCallback;

        return $this;
    }

    public function renderTitleText($primaryKeyValue)
    {
        if ($this->renderTitleTextCallback) {
            $callback = $this->renderTitleTextCallback;
            return $callback($primaryKeyValue);
        } else {
            return parent::renderTitleText($primaryKeyValue);
        }
    }
}
