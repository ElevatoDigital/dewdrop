<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Admin\Page\Stock;

use Dewdrop\Admin\Component\ComponentAbstract;
use Dewdrop\Admin\Component\CrudInterface;
use Dewdrop\Admin\Page\PageAbstract;

/**
 * Generate a CSV export for a CRUD component's Listing.
 */
class Export extends PageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Ensure permissions are correctly set and then pass the component
     * along to the view.
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('export');
        $this->view->assign('component', $this->component);
    }
}
