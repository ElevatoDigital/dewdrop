<?php

namespace Dewdrop\Fields\Helper\CsvCell;

use Dewdrop\Fields\Field;
use PHPUnit_Framework_TestCase;

class HeaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Header
     */
    protected $csvCellHeaderFieldsHelper;

    /**
     * @var Field
     */
    protected $field;

    protected function setUp()
    {
        $this->csvCellHeaderFieldsHelper = new Header();

        $this->field = new Field();
        $this->field
            ->setId('id')
            ->setLabel('label');
    }

    public function testUsage()
    {
        $output = $this->csvCellHeaderFieldsHelper->render($this->field);

        $this->assertInternalType('string', $output);
        $this->assertSame($this->field->getLabel(), $output);
    }
}