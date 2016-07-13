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
use Dewdrop\Admin\Component\SortableListingInterface;
use Dewdrop\Admin\ResponseHelper\Standard as ResponseHelper;
use Dewdrop\Exception;

/**
 * This page handles the saving of new sort order values for a component that
 * implements the SortableListingInterface.
 */
class SortListing extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract|SortableListingInterface
     */
    protected $component;

    /**
     * A JSON-encoded error, if processing failed for some reason.
     *
     * @var string
     */
    private $error;

    /**
     * Ensure the component implements the SortableListingInterface and that the
     * user is allowed to access the listing for the component.
     *
     * @throws Exception
     */
    public function init()
    {
        $this->component->getPermissions()->haltIfNotAllowed('view-listing');

        if (!$this->component instanceof SortableListingInterface) {
            throw new Exception('Component must implement SortableListingInterface');
        }
    }

    /**
     * Save newly POSTed sort order for the listing.
     *
     * @param ResponseHelper $responseHelper
     */
    public function process(ResponseHelper $responseHelper)
    {
        if (!$this->request->isPost()) {
            $this->error = ['result' => 'error', 'message' => 'Must be POST.'];
            return;
        }

        if (!is_array($this->request->getPost('sort_order'))) {
            $this->error = ['result' => 'error', 'message' => 'sort_order array not available.'];
            return;
        }

        $listing    = $this->component->getListing();
        $primaryKey = $listing->getPrimaryKey();
        $model      = $primaryKey->getTable();
        $dbAdapter  = $model->getAdapter();
        $sortField  = $this->component->getSortField();

        foreach ($this->request->getPost('sort_order') as $index => $id) {
            try {
                $model->update(
                    array($sortField->getName() => $index),
                    $dbAdapter->quoteInto(
                        "{$primaryKey->getName()} = ?",
                        $id
                    )
                );
            } catch (Exception $e) {
                $this->error = ['result' => 'error', 'message' => 'Failed to save.'];
            }
        }
    }

    /**
     * Send a response back to the XHR -- either an error message or a
     * success indicator.
     */
    public function render()
    {
        return (null === $this->error ? ['result' => 'success'] : $this->error);
    }
}
