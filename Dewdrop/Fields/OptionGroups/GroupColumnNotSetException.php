<?php

namespace Dewdrop\Fields\OptionGroups;

use Dewdrop\Exception;
use Dewdrop\Exception\DocInterface;
use Dewdrop\View\View;

class GroupColumnNotSetException extends Exception implements DocInterface
{
    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var string
     */
    private $tableName;

    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function getSummary()
    {
        $view = new View();
        $view
            ->setScriptPath(__DIR__ . '/view-scripts')
            ->assign('tableName', $this->tableName)
            ->assign('columns', $this->columns);
        return $view->render('group-column-not-set-summary.phtml');
    }

    public function getExamples()
    {
        $view = new View();
        $view->setScriptPath(__DIR__ . '/view-scripts');
        return $view->render('group-column-not-set-examples.phtml');
    }
}
