<?php

namespace Dewdrop\ActivityLog\Handler;

use Dewdrop\ActivityLog;
use Dewdrop\ActivityLog\Entity;
use Dewdrop\Db\Table;

abstract class HandlerAbstract implements HandlerInterface
{
    /**
     * @var ActivityLog
     */
    private $activityLog;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @var string
     */
    private $icon;

    /**
     * @var Table
     */
    private $model;

    /**
     * @var string
     */
    private $modelClass;

    /**
     * HandlerAbstract constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    abstract public function init();

    public function setActivityLog(ActivityLog $activityLog)
    {
        $this->activityLog = $activityLog;

        return $this;
    }

    public function write($summary, $message)
    {
        $this->activityLog->write($summary, $message);
        return $this;
    }

    public function log($summary, $message)
    {
        $this->activityLog->log($summary, $message);
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullyQualifiedName()
    {
        return '/application/activity-log/' . $this->name;
    }

    public function addAlias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    public function setModel(Table $model)
    {
        $this->model = $model;

        return $this;
    }

    public function getModel()
    {
        if (!$this->model) {
            $className   = $this->modelClass;
            $this->model = new $className();
        }

        return $this->model;
    }

    public function renderTitleText($primaryKeyValue)
    {
        $model   = $this->getModel();
        $columns = $model->getMetadata('columns');
        $row     = $model->find($primaryKeyValue);

        $out = '';

        if (array_key_exists('name', $columns)) {
            $out = $row->get('name');
        } elseif (array_key_exists('title', $columns)) {
            $out = $row->get('title');
        } elseif (array_key_exists('first_name', $columns) && array_key_exists('last_name', $columns)) {
            $out = "{$row->get('first_name')} {$row->get('last_name')}";
        } elseif (array_key_exists('username', $columns)) {
            $out = $row->get('username');
        }

        if (!$out) {
            $out = "{$model->getSingularTitle()} #{$primaryKeyValue}";
        }

        return $out;
    }

    public function createEntity($primaryKeyValue)
    {
        return new Entity($this, $primaryKeyValue);
    }
}
