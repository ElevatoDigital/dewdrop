<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Helper;

use Dewdrop\Fields\Helper\EditControl\Control;
use Dewdrop\Fields\Helper\EditControl\Label;
use Dewdrop\View\View;

class EditControl
{
    private $controlRenderer;

    private $labelRenderer;

    private $view;

    public function __construct(View $view)
    {
        $this->view = $view;

        $this->controlRenderer = new Control($view);
        $this->labelRenderer   = new Label($view);
    }

    public function getControlRenderer()
    {
        return $this->controlRenderer;
    }

    public function getLabelRenderer()
    {
        return $this->labelRenderer;
    }
}
