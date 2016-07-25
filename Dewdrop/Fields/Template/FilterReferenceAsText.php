<?php

namespace Dewdrop\Fields\Template;

use Dewdrop\Db\Field;
use Dewdrop\Db\Select;
use Dewdrop\Db\Select\Filter\Text as TextFilter;
use Dewdrop\Exception;

/**
 * A template that configures a reference field to use text filtering.  This
 * is often more useful when the reference field would have an unwieldy, large
 * number of options.  Instead,
 */
class FilterReferenceAsText
{
    public function __invoke(Field $field)
    {
        if (!$field->isType('reference')) {
            throw new Exception('The FilterReferenceAsText template can only be used on DB reference fields.');
        }

        $reference   = $field->getOptionPairsReference();
        $titleColumn = $field->getOptionPairs()->detectTitleColumn();

        $field
            ->assignHelperCallback(
                'SelectFilter.FilterType',
                function () {
                    return ['type' => 'text', 'options' => []];
                }
            )
            ->assignHelperCallback(
                'SelectFilter.SelectModifier',
                function ($helper, Select $select, $conditionSetName, $queryVars) use ($reference, $titleColumn) {
                    $filter = new TextFilter($reference['table'], $titleColumn);
                    return $filter->apply($select, $conditionSetName, $queryVars);
                }
            );
    }
}
