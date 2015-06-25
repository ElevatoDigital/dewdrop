<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\View\Helper;

use Dewdrop\Exception;
use Dewdrop\Fields\Helper\TableCell\Content as TableCell;
use Dewdrop\Fields\RowCollectionEditor;

/**
 * This view helper provides the default implementation for rendering a
 * RowCollectionEditor in either a table view or detail view context.
 * You can override either mode view the tableViewCallback or
 * detailsViewCallback options.  The callbacks should expect the same
 * arguments that the defaults (i.e. renderTableView() and renderDetailView())
 * in this class.
 */
class RowCollectionCellContent extends AbstractHelper
{
    /**
     * Render the table cell content for the supplied RowCollectionEditor.
     * The following options are needed:
     *
     * 1) renderer: \Dewdrop\Fields\TableCell\Helper\Content
     * 2) rowData: array (The data being used for rendering)
     * 3) mapping: string (The index in rowData containing the content)
     *
     * @param RowCollectionEditor $editor
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function direct(RowCollectionEditor $editor, array $options)
    {
        if (isset($options['renderer']) && $options['renderer'] instanceof TableCell) {
            $renderer = $options['renderer'];
        } else {
            throw new Exception("The 'renderer' option is required by RowCollectionCellContent.");
        }

        if (isset($options['rowData']) && is_array($options['rowData'])) {
            $rowData = $options['rowData'];
        } else {
            throw new Exception("The 'rowData' option is required by RowCollectionCellContent.");
        }

        if (isset($options['mapping']) && is_string($options['mapping'])) {
            $mapping = $options['mapping'];
        } else {
            throw new Exception("The 'mapping' option is required by RowCollectionCellContent.");
        }

        if (isset($options['detailViewCallback']) && is_callable($options['detailViewCallback'])) {
            $detailViewCallback = $options['detailViewCallback'];
        } else {
            $detailViewCallback = [$this, 'renderDetailView'];
        }

        if (isset($options['tableViewCallback']) && is_callable($options['tableViewCallback'])) {
            $tableViewCallback = $options['tableViewCallback'];
        } else {
            $tableViewCallback = [$this, 'renderTableView'];
        }

        /* @var $renderer TableCell */
        if ($renderer->isDetailView()) {
            return call_user_func($detailViewCallback, $editor, $renderer);
        } else {
            return call_user_func($tableViewCallback, $editor, $renderer, $rowData, $mapping);
        }
    }

    /**
     * A default rendering of a detail view for a RowCollectionEditor.
     * Renders an actual bootstrapTable containing the full data from
     * the RowCollection.
     *
     * @param RowCollectionEditor $editor
     * @param TableCell $renderer
     * @return mixed|null
     */
    public function renderDetailView(RowCollectionEditor $editor, TableCell $renderer)
    {
        $data = $editor->getData();

        if (!count($data)) {
            return null;
        } else {
            return $renderer->getView()->bootstrapTable($editor->getFields(), $data);
        }
    }

    /**
     * Default rendering for a table view context.  Expects a count of
     * items to be present in $rowData in the $mapping index.  Renders
     * that count concatenated with the plural title from the
     * RowCollectionEditor.
     *
     * @param RowCollectionEditor $editor
     * @param TableCell $renderer
     * @param array $rowData
     * @param $mapping
     * @return string
     */
    public function renderTableView(RowCollectionEditor $editor, TableCell $renderer, array $rowData, $mapping)
    {
        $count = $rowData[$mapping];
        $view  = $renderer->getView();

        if (1 === $count) {
            $title = $editor->getSingularTitle();
        } else {
            $title = $editor->getPluralTitle();
        }

        return sprintf('%d %s', $view->escapeHtml($count), $view->escapeHtml($title));
    }
}
