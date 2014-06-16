<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Env;

use Dewdrop\Admin\Component\ComponentAbstract;
use Zend\View\Helper\HeadScript;

interface EnvInterface
{
    public function registerComponentsInPath($path = null);

    public function registerComponent($folder);

    public function renderLayout($content, HeadScript $headScript = null);

    public function url(ComponentAbstract $component, $page, array $params = array());

    public function initComponent(ComponentAbstract $component);

    public function redirect($url);
}
