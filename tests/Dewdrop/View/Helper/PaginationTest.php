<?php

namespace Dewdrop\View\Helper;

use Dewdrop\Test\BaseTestCase;
use Dewdrop\View\View;

class PaginationTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View(null);
        $_SERVER['REQUEST_URI'] = null; // this is needed for the $this->view->getRequest in getUrl
    }

    /**
     * first and last list item must be disabled
     */
    public function testNextAndPreviousButtonsGetDisabled()
    {
        /* first and last list item must be disabled */
        $out = $this->view->pagination(11, 10, 1);
        $this->assertMatchesDomQuery('ul[class="pagination"]/li[1][class="disabled"]', $out);

        $out = $this->view->pagination(11, 10, 2);
        $index = 4; //last item in pagination list
        $this->assertMatchesDomQuery("ul[class=\"pagination\"]/li[{$index}][class=\"disabled\"]", $out);
    }

    public function testAlwaysShowFirstAndLast2AndMiddle6Pages()
    {
        $out = $this->view->pagination(3000, 10, 20);

        $result = $this->queryDom('ul/li[2]/a', $out);
        $this->assertEquals('1', $result->current()->textContent);

        $result = $this->queryDom('ul/li[3]/a', $out);
        $this->assertEquals('2', $result->current()->textContent);

        $this->assertMatchesDomQuery('ul/li[4][class="disabled"]', $out);

        $result = $this->queryDom('ul/li[5]/a', $out);
        $this->assertEquals('17', $result->current()->textContent);

        $result = $this->queryDom('ul/li[6]/a', $out);
        $this->assertEquals('18', $result->current()->textContent);

        $result = $this->queryDom('ul/li[7]/a', $out);
        $this->assertEquals('19', $result->current()->textContent);

        $result = $this->queryDom('ul/li[8]/a', $out);
        $this->assertEquals('20', $result->current()->textContent);

        $result = $this->queryDom('ul/li[9]/a', $out);
        $this->assertEquals('21', $result->current()->textContent);

        $result = $this->queryDom('ul/li[10]/a', $out);
        $this->assertEquals('22', $result->current()->textContent);

        $result = $this->queryDom('ul/li[11]/a', $out);
        $this->assertEquals('23', $result->current()->textContent);

        $this->assertMatchesDomQuery('ul/li[12][class="disabled"]', $out);

        $result = $this->queryDom('ul/li[13]/a', $out);
        $this->assertEquals('299', $result->current()->textContent);

        $result = $this->queryDom('ul/li[14]/a', $out);
        $this->assertEquals('300', $result->current()->textContent);
    }

    public function testShowLastTenPages()
    {
        $out = $this->view->pagination(1000, 10, 98);

        $index = 5;
        for ($i=90; $i <= 100; $i++, $index++) {
            $result = $this->queryDom("ul/li[{$index}]/a", $out);
            $this->assertEquals($i, $result->current()->textContent);
        }
    }
}
