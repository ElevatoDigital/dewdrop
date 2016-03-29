<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Page\PageAbstract;
use ReflectionClass;

/**
 * A class other stock pages can extend to allow their view scripts to be used even
 * when a page controller sub-class is present for the page.  So, you can add an
 * Index.php to your admin component that extends the stock index but still use the
 * stock index.phtml view script if you don't need a custom view script for your
 * use case.
 */
abstract class StockPageAbstract extends PageAbstract
{
    /**
     * @var string
     */
    private $classLocation;

    public function renderView()
    {
        $scriptFilename = $this->inflectViewScriptName();

        if (!file_exists($this->viewScriptPath . '/' . $scriptFilename)) {
            if ($this->stockSubclassHasViewScript($scriptFilename)) {
                $this->view->setScriptPath($this->getClassLocation() . '/view-scripts');
            } else {
                $this->view->setScriptPath(__DIR__ . '/view-scripts');
            }
        }

        return parent::renderView();
    }

    private function stockSubclassHasViewScript($scriptFilename)
    {
        return file_exists($this->getClassLocation() . '/view-scripts/' . $scriptFilename);
    }

    private function getClassLocation()
    {
        if (!$this->classLocation) {
            $class       = new ReflectionClass($this);
            $parentClass = $class->getParentClass();
            $this->classLocation = dirname($parentClass->getFileName());
        }

        return $this->classLocation;
    }
}
