<?php

namespace Dewdrop\ActivityLog\Handler\Event;

use Dewdrop\ActivityLog\Handler\TableHandler;
use Dewdrop\Db\Field as DbField;
use Dewdrop\Db\Row;
use Dewdrop\Db\Table;
use Dewdrop\Fields\FieldInterface;

class FieldChange
{
    /**
     * @var TableHandler
     */
    private $logHandler;

    /**
     * @var string|FieldInterface
     */
    private $field;

    /**
     * @var callable
     */
    private $eventHandler;

    /**
     * @var mixed
     */
    private $originalValue;

    public function __construct(TableHandler $logHandler, $field, callable $eventHandler)
    {
        $this->logHandler   = $logHandler;
        $this->field        = $field;
        $this->eventHandler = $eventHandler;
    }

    public function getLogHandler()
    {
        return $this->logHandler;
    }

    public function getLog()
    {
        return $this->getLogHandler();
    }

    public function getField()
    {
        $this->initializeField();
        return $this->field;
    }

    public function log($summary, $message)
    {
        $this->getLog()->log($summary, $message);

        return $this;
    }

    public function write($summary, $message)
    {
        return $this->log($summary, $message);
    }

    public function storeValuePriorToChange()
    {
        $this->initializeField();

        $this->originalValue = $this->field->getValue();

        return $this;
    }

    public function trigger(Row $row)
    {
        $this->initializeField();

        if ($this->valueChanged()) {
            call_user_func($this->eventHandler, $this, $row);
        }

        return $this;
    }

    public function manyToManyMessage(Row $subject, Table $valueTable)
    {
        return new ManyToManyMessage($subject, $valueTable, $this->field, $this->originalValue);
    }

    private function initializeField()
    {
        if (is_string($this->field)) {
            $this->field = $this->logHandler->getTable()->field($this->field);
        }
    }

    private function valueChanged()
    {
        if ($this->field instanceof DbField && !$this->field->hasRow()) {
            return false;
        }

        $newValue = $this->field->getValue();

        if (is_array($this->originalValue) && is_array($newValue)) {
            $intersection = count(array_intersect($this->originalValue, $newValue));
            return count($this->originalValue) !== $intersection || count($newValue) !== $intersection;
        } else {
            return $this->originalValue !== $newValue;
        }
    }
}
