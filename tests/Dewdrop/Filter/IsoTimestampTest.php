<?php

namespace Dewdrop\Filter;

class IsoTimestampTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IsoTimestamp
     */
    private $filter;

    public function setUp()
    {
        $this->filter = new IsoTimestamp();
    }

    /**
     * @return array
     */
    public function emptyValueDataProvider()
    {
        return [
            [null],
            [''],
            [' '],
        ];
    }

    /**
     * @dataProvider emptyValueDataProvider()
     * @param mixed $value
     */
    public function testEmptyValueReturnsNull($value)
    {
        $this->assertNull($this->filter->filter($value));
    }

    public function testValueIsPassedToStrtotimeForParsing()
    {
        $this->assertEquals('2012-05-09 15:00:00', $this->filter->filter('may 9 2012 3pm'));
    }
}
