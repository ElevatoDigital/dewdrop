<?php

namespace Dewdrop\Db\Adapter;

use Dewdrop\Exception;
use Dewdrop\Exception\DocInterface;
use Dewdrop\View\View;

class GroupKeyNotPresentInResultsetException extends Exception implements DocInterface
{
    /**
     * @var string
     */
    private $groupKey;

    /**
     * @var array
     */
    private $validationRow;

    public function setGroupKey($groupKey)
    {
        $this->groupKey = $groupKey;

        return $this;
    }

    public function setValidationRow(array $validationRow)
    {
        $this->validationRow = $validationRow;

        return $this;
    }

    public function getSummary()
    {
        $view = new View();
        $view
            ->setScriptPath(__DIR__ . '/view-scripts')
            ->assign('validationRow', $this->validationRow)
            ->assign('groupKey', $this->groupKey);
        return $view->render('group-key-not-present-in-resultset-summary.phtml');
    }

    public function getExamples()
    {
        $view = new View();
        $view->setScriptPath(__DIR__ . '/view-scripts');
        return $view->render('group-key-not-present-in-resultset-examples.phtml');
    }
}
