<?php

namespace Dewdrop\ActivityLog\Handler;

use Dewdrop\ActivityLog;
use Dewdrop\ActivityLog\Handler\Event\FieldChange as FieldChangeEvent;
use Dewdrop\Db\Field;
use Dewdrop\Db\Row;
use Dewdrop\Db\Table;
use Dewdrop\Exception;
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
     * @var array
     */
    private $fieldChangeEvents = [];

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

        if (!$tableName) {
            $className = get_class($table);
            throw new Exception("Cannot create activity log handle for {$className} because no table name is set.");
        }

        $this
            ->setActivityLog(($activityLog ?: Pimple::getResource('activity-log')))
            ->setName($inflectedName)
            ->setModel($table)
            ->addAlias($tableName);

        parent::__construct();
    }

    public function init()
    {

    }

    public function getTable()
    {
        return $this->table;
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

    public function onFieldChange($field, callable $handler)
    {
        $this->fieldChangeEvents[] = new FieldChangeEvent($this, $field, $handler);

        return $this;
    }

    public function prepareFieldChangeEvents(Field $field)
    {
        /* @var $event FieldChangeEvent */
        foreach ($this->fieldChangeEvents as $event) {
            if ($event->getField() === $field) {
                $event->storeValuePriorToChange();
            }
        }

        return $this;
    }

    public function triggerFieldChangeEvents(Row $row)
    {
        /* @var $event FieldChangeEvent */
        foreach ($this->fieldChangeEvents as $event) {
            $event->trigger($row);
        }

        return $this;
    }
}
