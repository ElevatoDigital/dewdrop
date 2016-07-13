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
use SqlFormatter;

/**
 * This page helps understand how your Listing's Select object is translated to
 * SQL.  It uses the getModifiedSelect() method of your Listing to ensure that
 * sorts, filters, etc. are applied and then pretty-prints the SQL.
 *
 * It might be cool to provide some basic profiling and index management features
 * on this page, but that might be tricky when factoring in cross-platform
 * support, etc.
 */
class DebugListingSql extends StockPageAbstract
{
    /**
     * The CRUD component.
     *
     * @var CrudInterface|ComponentAbstract
     */
    protected $component;

    /**
     * Get the processed SQL statement from the Listing, pretty print it, and
     * pass it to our view script.
     */
    public function render()
    {
        $this->component->getPermissions()->haltIfNotAllowed('debug');

        $select = $this->component->getListing()->getModifiedSelect($this->component->getFields());

        $this->view->formattedSql = SqlFormatter::format((string) $select);

        return $this->renderView();
    }
}
