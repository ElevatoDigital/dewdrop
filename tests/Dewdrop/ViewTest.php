<?php

namespace Dewdrop\View;

use Dewdrop\Test\BaseTestCase;

class ViewTest extends BaseTestCase
{
    private $view;

    public function setUp()
    {
        $this->view = new View();
    }

    public function testConstructorEscaperPokaYoke()
    {
        $this->assertTrue($this->view->getEscaper() instanceof \Zend\Escaper\Escaper);
    }

    public function testConstructorCallsInitMethod()
    {
        $view = $this->getMockBuilder('\Dewdrop\View\View')
            ->setMethods(array('init'))
            ->disableOriginalConstructor()
            ->getMock();

        $view->expects($this->once())
            ->method('init');

        $view->__construct();
    }

    public function testImplicitAssignAndRetrieveViaMagicMethods()
    {
        $this->view->var = 'test';

        $this->assertEquals('test', $this->view->var);
    }

    public function testRetrievingUnknownVarViaMagicMethod()
    {
        $this->assertEquals(null, $this->view->var);
    }

    public function testRetrieveAnAlreadyInstantiatedHelper()
    {
        $first = $this->view->helper('wpinputtext');

        $this->assertEquals(
            $first,
            $this->view->helper('WPINPUTTEXT')
        );
    }

    public function testCallMagicMethodCallsHelpersDirectMethod()
    {
        $this->assertTrue(is_string($this->view->wpInputText('name', '')));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testRetrieveUnknownHelperName()
    {
        $this->view->helper('asdfasdfasdfasdf');
    }

    public function testAssignMultipleVarsAtOnce()
    {
        $this->view->assign(
            array(
                'one'   => 1,
                'two'   => 2,
                'three' => 3,
                'false' => false
            )
        );

        $this->assertEquals(1, $this->view->one);
        $this->assertEquals(2, $this->view->two);
        $this->assertEquals(3, $this->view->three);
        $this->assertFalse($this->view->false);
    }

    public function testAssignChaining()
    {
        $this->view
            ->assign('single', 'single')
            ->assign(
                array(
                    'double' => 'double',
                    'triple' => 'triple'
                )
            );

        $this->assertEquals('single', 'single');
        $this->assertEquals('double', 'double');
        $this->assertEquals('triple', 'triple');
    }

    public function testGetHelperWithoutCall()
    {
        $this->assertEquals(
            'Dewdrop\View\Helper\WpInputText',
            get_class($this->view->helper('wpInputText'))
        );
    }

    // {{{ Testing falsey values passed to \Zend\Escaper\Escaper

    /**
     * The ZF2 escaper complains when it gets a null value because it claims
     * it can't convert it to utf-8.  This series of tests makes sure we
     * correctly convert falsey values to empty strings.
     */

    public function testEscapeHtmlWithFalseyValues()
    {
        $this->assertEquals('', $this->view->escapeHtml(false));
        $this->assertEquals('', $this->view->escapeHtml(null));
    }

    public function testEscapeHtmlAttrWithFalseyValues()
    {
        $this->assertEquals('', $this->view->escapeHtmlAttr(false));
        $this->assertEquals('', $this->view->escapeHtmlAttr(null));
    }

    public function testEscapeJsWithFalseyValues()
    {
        $this->assertEquals('', $this->view->escapeJs(false));
        $this->assertEquals('', $this->view->escapeJs(null));
    }

    public function testEscapeCssWithFalseyValues()
    {
        $this->assertEquals('', $this->view->escapeCss(false));
        $this->assertEquals('', $this->view->escapeCss(null));
    }

    public function testEscapeUrlWithFalseyValues()
    {
        $this->assertEquals('', $this->view->escapeUrl(false));
        $this->assertEquals('', $this->view->escapeUrl(null));
    }

    // }}}

    // {{{

    /**
     * This set of tests just makes sure the escaper is wired into the methods
     * like we expect.  It doesn't really stretch the escaper or try to get around
     * it.  We're assuming the implementation is sound, but just making sure we're
     * actually calling it.
     */

    public function testEscapeHtmlIsCalled()
    {
        $this->assertEquals(
            '&lt;a&gt;',
            $this->view->escapeHtml('<a>')
        );
    }

    public function testEscapeHtmlAttrIsCalled()
    {
        $this->assertEquals(
            '&quot;value&quot;',
            $this->view->escapeHtmlAttr('"value"')
        );
    }

    public function testEscapeUrlIsCalled()
    {
        $this->assertEquals(
            'http%3A%2F%2Fgoogle.com%2F%3Fvar%3D%20%20%20%20%20test',
            $this->view->escapeUrl('http://google.com/?var=     test')
        );
    }

    public function testEscapeCssIsCalled()
    {
        $this->assertEquals(
            'css\28 \29 \20 \3C test\3E ',
            $this->view->escapeCss('css() <test>')
        );
    }

    public function testEscapeJsIsCalled()
    {
        $this->assertEquals(
            '\x22\x20alert\x28\x22TEST\x22\x29\x3B',
            $this->view->escapeJs('" alert("TEST");')
        );
    }

    // }}}
}
