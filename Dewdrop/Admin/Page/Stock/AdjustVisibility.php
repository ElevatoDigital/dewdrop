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
use Dewdrop\Fields\Filter\Visibility as VisibilityFilter;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;

/**
 * This page handles the user's submission of the "Adjust Columns" dialog,
 * saving the user's selections back to a visibility filter so that they
 * can decide which columns should be visible on a listing.
 */
class AdjustVisibility extends StockPageAbstract
{
    /**
     * The CRUD component providing the visibility filter.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Grab the selected visible columns from POST and save them back to
     * the visibility filter.
     *
     * @param ResponseHelper $responseHelper
     */
    public function process(ResponseHelper $responseHelper)
    {
        $this->component->getPermissions()->haltIfNotAllowed('adjust-columns');

        $selections      = $this->request->getPost('visible_columns');
        $applyToAllUsers = (boolean) $this->request->getPost('apply_to_all_users', false);

        if (is_array($selections)) {
            /* @var $filter VisibilityFilter */
            $filter = $this->component->getVisibilityFilter();

            $filter->save($this->component->getFields(), $selections, $applyToAllUsers);
        }

        $responseHelper->redirectToAdminPage('index');
    }
}
