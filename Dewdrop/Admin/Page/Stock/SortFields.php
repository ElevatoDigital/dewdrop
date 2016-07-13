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
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Fields\Filter\Groups as GroupsFilter;

/**
 * This page provides a UI that enables a user to sort and group fields in a
 * component.  Once saved, the GroupsFilter will be used throughout the CRUD
 * to re-order the fields and put them into groups (usually a tab control),
 * allowing the user to make a large number of fields easier to comprehend.
 */
class SortFields extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * The GroupsFilter used to load/save settings for sorting and group of
     * the component's fields.
     *
     * @var GroupsFilter
     */
    private $filter;

    /**
     * Ensure the user is allowed to sort fields on this component.
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('sort-fields');

        $this->filter = $this->component->getFieldGroupsFilter();
    }

    /**
     * On POST requests, save the new settings to the filter, set a success
     * message and redirect back to the component's listing.
     *
     * @param ResponseHelper $responseHelper
     */
    public function process(ResponseHelper $responseHelper)
    {
        if ($this->request->isPost()) {
            $responseHelper->run(
                'save',
                function () {
                    $this->filter->save(
                        json_decode($this->request->getPost('sorted_fields'), true)
                    );
                }
            );

            $responseHelper
                ->setSuccessMessage("Successfully sorted and grouped {$this->component->getTitle()} fields")
                ->redirectToAdminPage('index');
        }
    }

    /**
     * Pass some dependencies into the View.
     */
    public function render()
    {
        $this->view->assign([
            'fieldGroups' => $this->filter->getConfigForFields($this->component->getFields()),
            'component'   => $this->component,
            'fields'      => $this->component->getFields()
        ]);

        return $this->renderView();
    }
}
