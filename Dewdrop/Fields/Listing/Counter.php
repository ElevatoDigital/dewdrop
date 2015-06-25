<?php

/**
 * Dewdrop
 *
 * @link      https://github.com/DeltaSystems/dewdrop
 * @copyright Delta Systems (http://deltasys.com)
 * @license   https://github.com/DeltaSystems/dewdrop/LICENSE
 */

namespace Dewdrop\Fields\Listing;

use Dewdrop\Fields;
use Dewdrop\Fields\FieldInterface;
use Dewdrop\Fields\Helper\CellRendererInterface;
use Dewdrop\Fields\Helper\CsvCell;
use Dewdrop\Fields\Helper\TableCell;
use Dewdrop\Fields\Listing;

/**
 * The Counter class assists in the creation of a simple kind of report that
 * comes up frequently in data-heavy projects: group by a field and display
 * the number of records that have a given value for that field.  For example,
 * if you had a database containing many people and their preferred shirt
 * sizes, you could supply this class with the shirt_size_id field object,
 * the full collection of Fields from your component and a Listing that would
 * normally fetch all the people and their shirt sizes.  Combining the data
 * from the fetchData() method in this class with the Fields from its
 * buildRenderFields() method, you could use those objects to easily render
 * a table showing how many users requested a Small shirt, a Medium shirt,
 * etc.
 *
 * To accomplish this task, this class disables the pagination helper on the
 * Listing and fetches all its data.  It then iterates over the data, rendering
 * the content for the $groupField using either a TableCell or CsvCell renderer
 * helper.  Using the output from that renderer, it groups the results and
 * counts them.  The Counter class takes this approach rather than attempting
 * to manipulate the Listing's SQL to add a GROUP BY and COUNT(*) because using
 * the rendered output maps more closely to the user's expectations, prevents
 * potential bugs when the rendering code for the grouping field references
 * multiple database columns, and avoids problems that could be introduced
 * if the original Listing SQL was already using aggregate functions.
 *
 * This design does, however, potentially introduce performance concerns if you
 * have a very large number of records returned from your Listing's SQL
 * statement.  Because _all_ records are fetched from the Listing and the contents
 * rendered for each of those records for the $groupField, you may not be able
 * to get an adequate response time out of this class without further caching.
 * So just keep that in mind before using Counter in your user-facing components.
 */
class Counter
{
    /**
     * The field by which records should be grouped before they are counted.
     *
     * @var FieldInterface
     */
    private $groupField;

    /**
     * The fields used when fetching data from the Listing.  Needed so the Listing can apply filters, etc.
     *
     * @var Fields
     */
    private $fields;

    /**
     * The Listing from which we'll fetch the raw data which will then be grouped and counted.
     *
     * @var Listing
     */
    private $listing;

    /**
     * Supply the dependencies for the Counter: a field by which the user would like to group the
     * Listing's data before counting, the Fields the Listing will use for filtering and sorting
     * when fetching data, and the Listing itself.
     *
     * @param FieldInterface $groupField
     * @param Fields $fields
     * @param Listing $listing
     */
    public function __construct(FieldInterface $groupField, Fields $fields, Listing $listing)
    {
        $this->groupField = $groupField;
        $this->fields     = $fields;
        $this->listing    = $listing;
    }

    /**
     * Build a Fields object that can be used when rendering the grouped counts.  Supplies a field
     * to render the actual HTML value from the original Fields object used when fetching data for
     * the listing and a field to render the count for that value.  We don't do any escaping here
     * because we assume that the original Fields object handled that in its rendering code.  The
     * fields object returned from this method supports both TableCell and CsvCell rendering.
     *
     * @return Fields
     */
    public function buildRenderFields()
    {
        $fields = new Fields();

        $fields
            ->add('content')
                ->setLabel($this->groupField->getLabel())
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell\Content $helper, array $rowData) {
                        // Not escaping here because we assume it was escaped by the original renderer in fetchData()
                        return $rowData['content'];
                    }
                )
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function (CsvCell\Content $helper, array $rowData) {
                        return $rowData['content'];
                    }
                )
            ->add('count')
                ->setLabel('Count')
                ->setVisible(true)
                ->assignHelperCallback(
                    'TableCell.Content',
                    function (TableCell\Content $helper, array $rowData) {
                        return $helper->getView()->escapeHtml($rowData['count']);
                    }
                )
                ->assignHelperCallback(
                    'CsvCell.Content',
                    function (CsvCell\Content $helper, array $rowData) {
                        return $rowData['count'];
                    }
                );

        return $fields;
    }

    /**
     * This is the core of the Counter class.  The overall logic implemented in
     * this method is explained in detail in the primary docblock for this class.
     * The array returned from this method will contain one associative array
     * for each distinct value of the $groupField found: the content for that
     * field as generated by the supplied $renderer and the count of records
     * that had that value.
     *
     * @param CellRendererInterface $renderer
     * @return array
     */
    public function fetchData(CellRendererInterface $renderer)
    {
        /* @var $sorter \Dewdrop\Fields\Helper\SelectSort */
        $sorter = $this->listing->getSelectModifierByName('SelectSort');

        $sorter
            ->setDefaultField($this->groupField)
            ->setDefaultDirection('asc');

        $this->listing->removeSelectModifierByName('SelectPaginate');

        $data    = $this->listing->fetchData($this->fields);
        $counts  = [];
        $content = [];
        $out     = [];

        foreach ($data as $row) {
            $cellContent = $renderer->getContentRenderer()->render($this->groupField, $row, 0, 0);
            $contentHash = md5(strtoupper($cellContent));

            if (!array_key_exists($contentHash, $counts)) {
                $counts[$contentHash] = 0;
            }

            $counts[$contentHash] += 1;

            $content[$contentHash] = $cellContent;
        }

        foreach ($counts as $hash => $count) {
            $out[] = ['content' => $content[$hash], 'count' => $count];
        }

        return $out;
    }
}
