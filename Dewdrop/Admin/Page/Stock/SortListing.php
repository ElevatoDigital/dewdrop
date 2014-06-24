<?php

namespace Dewdrop\Admin\Page\Stock;

use Exception;
use Dewdrop\Admin\Component\SortableListingInterface;
use Dewdrop\Admin\Page\PageAbstract;

class SortListing extends PageAbstract
{
    private $error;

    public function init()
    {
        if (!$this->component instanceof SortableListingInterface) {
            throw new Exception('Component must implement SortableListingInterface');
        }
    }

    public function process($responseHelper)
    {
        if (!$this->request->isPost()) {
            $this->error = json_encode(['result' => 'error', 'message' => 'Must be POST.']);
            return false;
        }

        if (!is_array($this->request->getPost('sort_order'))) {
            $this->error = json_encode(['result' => 'error', 'message' => 'sort_order array not available.']);
            return false;
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
                $this->error = json_encode(['result' => 'error', 'message' => 'Failed to save.']);
                return false;
            }
        }
    }

    public function render()
    {
        header('Content-Type: application/json');
        $this->component->setShouldRenderLayout(false);
        return (null === $this->error ? json_encode(['result' => 'success']) : $this->error);
    }
}
