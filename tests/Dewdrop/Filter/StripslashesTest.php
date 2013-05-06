<?php

namespace Dewdrop\Filter;

use Dewdrop\Test\BaseTestCase;

class StripslashesTest extends BaseTestCase
{
    public function testFilterRemovesSlashesBeforeSingleQuoteCharacters()
    {
        $value  = "This is a \'test\'";
        $filter = new Stripslashes();

        return $this->assertEquals("This is a 'test'", $filter->filter($value));
    }
}
