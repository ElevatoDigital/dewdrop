<?php

namespace Dewdrop\Fields\OptionPairs;

use Dewdrop\Exception;
use Dewdrop\Exception\DocInterface;
use Dewdrop\View\View;

class TitleColumnNotDetectedException extends Exception implements DocInterface
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $columns;

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function getSummary()
    {
        $view = new View();
        $view
            ->setScriptPath(__DIR__ . '/view-scripts')
            ->assign('tableName', $this->tableName)
            ->assign('columns', $this->columns);
        return $view->render('title-column-not-detected-summary.phtml');
    }

    public function getExamples()
    {
        $view = new View();
        $view->setScriptPath(__DIR__ . '/view-scripts');
        return $view->render('title-column-not-detected-examples.phtml');
    }
}
