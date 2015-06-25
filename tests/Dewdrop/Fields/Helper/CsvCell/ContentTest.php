<?php

namespace Dewdrop\Fields\Helper\CsvCell;

use PHPUnit_Framework_TestCase;

class ContentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Content
     */
    protected $csvCellContentFieldsHelper;

    /**
     * @var \Dewdrop\Db\Field|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $field;

    protected function setUp()
    {
        $this->csvCellContentFieldsHelper = new Content();

        $this->field = $this->getMockBuilder('\Dewdrop\Db\Field')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return array
     */
    public function providerUsage()
    {
        static $row = [
            'active'       => true,
            'state_id'     => 42,
            'state'        => 'Tennessee',
            'date_created' => '2014-06-12',
            'date_updated' => '2014-06-12 09:39:42',
            'event'        => 'Some Thing',
        ];

        return [
            [$row, 'active', 'boolean', 'Yes'],
            [$row, 'state_id', 'reference', 'Tennessee'],
            [$row, 'date_created', 'date', 'Jun 12, 2014'],
            [$row, 'date_updated', 'timestamp', 'Jun 12, 2014 9:39AM'],
            [$row, 'event', 'string', 'Some Thing'],
        ];
    }

    /**
     * @dataProvider providerUsage
     * @param array $row
     * @param string $name
     * @param string $type
     * @param string $outputExpected
     * @return void
     */
    public function testUsage($row, $name, $type, $outputExpected)
    {
        $this->field
            ->expects($this->any())
            ->method('isType')
            ->will($this->returnCallback(function () use ($type) {
                return in_array($type, func_get_args(), true);
            }));

        $this->field
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $output = $this->csvCellContentFieldsHelper->render($this->field, $row, 0, 0);
        $this->assertInternalType('string', $output);
        $this->assertSame($outputExpected, $output);
    }
}