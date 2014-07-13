<?php

namespace Dewdrop\Admin\Page;

use Dewdrop\Test\DbTestCase;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
use Dewdrop\Pimple;
use Dewdrop\Request;

class EditAbstractTest extends DbTestCase
{
    private $db;

    private $paths;

    private $request;

    private $page;

    public function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/../test-models/Animals.php';

        $testPimple = new \Pimple();
        $testPimple['dewdrop-request'] = new Request();

        require_once __DIR__ . '/../test-components/animals/Component.php';
        $this->component = new \DewdropTest\Admin\Animals\Component($testPimple);

        $file = __DIR__ . '/../test-components/animals/Edit.php';
        require_once $file;

        $this->request = $this->component->getRequest();
        $this->paths   = $this->component->getPaths();

        $this->page = new \DewdropTest\Admin\Animals\Edit(
            $this->component,
            $this->component->getRequest(),
            $file
        );
    }

    public function getDataSet()
    {
        return $this->createXmlDataSet(__DIR__ . '/../datasets/animals.xml');
    }

    public function testCanFindRowBasedUponQueryStringParam()
    {
        $this->request->setQuery('dewdrop_test_animal_id', 2);

        $row = $this->page->findRowById('\DewdropTest\Model\Animals');

        $this->assertEquals(2, $row->get('dewdrop_test_animal_id'));
        $this->assertEquals('Raptor', $row->get('name'));
        $this->assertEquals(1, $row->get('is_fierce'));
    }

    public function testWillCreateNewRowIfQueryStringIsEmpty()
    {
        $row = $this->page->findRowById('\DewdropTest\Model\Animals');

        $this->assertNull($row->get('dewdrop_test_animal_id'));
    }

    public function testViewTitleIsSetByFindRowByIdMethod()
    {
        $this->request->setQuery('dewdrop_test_animal_id', 2);

        $model = new \DewdropTest\Model\Animals(Pimple::getResource('db'));
        $row   = $this->page->findRowById('\DewdropTest\Model\Animals');

        $this->assertEquals(
            'Edit ' . $model->getSingularTitle(),
            $this->page->getView()->title
        );

        $this->request->setQuery('dewdrop_test_animal_id', null);

        $row = $this->page->findRowById('\DewdropTest\Model\Animals');

        $this->assertEquals(
            'Add New ' . $model->getSingularTitle(),
            $this->page->getView()->title
        );
    }

    public function testWillProcessIfRequestMethodIsPost()
    {
        $this->request->setMethod('POST');

        $this->assertTrue($this->page->shouldProcess());
    }

    public function testWontProcessIfRequestIsNotPost()
    {
        $this->request->setMethod('GET');

        $this->assertFalse($this->page->shouldProcess());
    }

    public function testInputFilterGeneratesErrorsAccurately()
    {
        $filter = $this->page->getInputFilter();
        $name   = new \Zend\InputFilter\Input('name');
        $email  = new \Zend\InputFilter\Input('email');
        $valid  = new \Zend\InputFilter\Input('valid_input');

        $name->getValidatorChain()
            ->addValidator(new \Zend\Validator\NotEmpty());

        $filter->add($name);

        $email->getValidatorChain()
            ->addValidator(new \Zend\Validator\EmailAddress());

        $filter->add($email);

        $valid->getValidatorChain()
            ->addValidator(new \Zend\Validator\NotEmpty());

        $filter->add($valid);

        $filter->setData(
            array(
                'name'         => null,
                'email'        => 'test',
                'not_an_input' => null,
                'valid_input'  => 'test'
            )
        );

        $filter->isValid();

        $this->assertEquals(2, count($this->page->getErrorsFromInputFilter()));
    }

    public function testErrorMessagesRelatedToFieldsHaveTitlePrefix()
    {
        $model = new \DewdropTest\Model\Animals();
        $row   = $model->createRow();

        $this->request
            ->setMethod('POST')
            ->setPost(
                array(
                    'name'      => null,
                    'is_fierce' => 1
                )
            );

        $this->page->getFields()
            ->add($row->field('name'), 'animals')
            ->add($row->field('is_fierce'));

        $this->page->shouldProcess();
        $this->page->getInputFilter()->isValid();

        $this->assertEquals(1, count($this->page->getErrorsFromInputFilter()));
        $this->assertTrue($this->page->fieldHasError('animals:name'));
    }

    /**
     * @expectedException \Dewdrop\Exception
     */
    public function testCheckUnknownFieldForErrorThrowsException()
    {
        $this->page->fieldHasError('test');
    }
}
