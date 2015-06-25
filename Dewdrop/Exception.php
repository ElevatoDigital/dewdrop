<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop;

use Dewdrop\Exception\DocInterface;
use Dewdrop\Exception\View\View;

/**
 * A simple exception sub-class used throughout Dewdrop to make it easy to
 * distinguish exceptions thrown by Dewdrop itself from those thrown by other
 * libraries.
 */
class Exception extends \Exception
{
    public function render()
    {
        $view = new View();

        if ($this instanceof DocInterface) {
            $view
                ->assign('summary', $this->getSummary())
                ->assign('examples', $this->getExamples());
        }

        $view
            ->setScriptPath(__DIR__ . '/Exception/View/view-scripts/')
            ->assign('isGenericException', (!$this instanceof DocInterface))
            ->assign('exceptionClass', get_class($this))
            ->assign('message', $this->message)
            ->assign('trace', $this->getTrace());

        return $view->render('exception.phtml');
    }
}
