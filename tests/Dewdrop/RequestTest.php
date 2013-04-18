<?php

namespace Dewdrop;

use Dewdrop\Test\BaseTestCase;

class RequestTest extends BaseTestCase
{
    private $request;

    public function setUp()
    {
        $this->request = new Request(
            array(
                'postVar1' => 1,
                'postVar2' => 2
            ),
            array(
                'queryVar1' => 1,
                'queryVar2' => 2
            ),
            'POST'
        );
    }

    public function testGetPostWithoutIndexReturnsFullArray()
    {
        $post = $this->request->getPost();

        $this->assertEquals(2, count($post));
        $this->assertEquals('postVar1', current(array_keys($post)));
    }

    public function testGetQueryWithoutIndexReturnsFullArray()
    {
        $query = $this->request->getQuery();

        $this->assertEquals(2, count($query));
        $this->assertEquals('queryVar1', current(array_keys($query)));
    }

    public function testGetUnkownPostVarReturnsNullWhenNoDefault()
    {
        $this->assertNull($this->request->getPost('fafafafafa'));
    }

    public function testGetUnkownQueryVarReturnsNullWhenNoDefault()
    {
        $this->assertNull($this->request->getQuery('fafafafafa'));
    }

    public function testGetUnkownPostVarReturnsDefault()
    {
        $this->assertEquals('foobar', $this->request->getPost('fafafafafa', 'foobar'));
    }

    public function testGetUnkownQueryVarReturnsDefault()
    {
        $this->assertEquals('foobar', $this->request->getQuery('fafafafafa', 'foobar'));
    }

    public function testGetKnownPostVarReturnsCorrectValue()
    {
        $this->assertEquals(1, $this->request->getPost('postVar1'));
        $this->assertEquals(2, $this->request->getPost('postVar2'));
    }

    public function testGetKnownQueryVarReturnsCorrectValue()
    {
        $this->assertEquals(1, $this->request->getQuery('queryVar1'));
        $this->assertEquals(2, $this->request->getQuery('queryVar2'));
    }

    public function testIsPostWorksWhenMethodIsPost()
    {
        $this->assertTrue($this->request->isPost());
    }

    public function testIsPostWorksWhenMethodIsNotPost()
    {
        $request = new Request(array(), array(), 'GET');
        $this->assertFalse($request->isPost());
    }
}
