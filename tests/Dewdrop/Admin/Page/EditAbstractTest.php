<?php

namespace Dewdrop\Admin\Page;

use Dewdrop\Db\Test\DbTestCase;
use Dewdrop\Db\Adapter;
use Dewdrop\Paths;
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

        $this->paths   = new Paths();
        $this->request = new Request();

        $wpdb = new \wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
        $this->db = new Adapter($wpdb);

        require_once __DIR__ . '/../test-models/Animals.php';

        require_once __DIR__ . '/../test-components/animals/Component.php';
        $this->component = new \DewdropTest\Admin\Animals\Component($this->db, $this->paths, $this->request);

        $file = __DIR__ . '/../test-components/animals/Edit.php';
        require_once $file;

        $this->page = new \DewdropTest\Admin\Animals\Edit(
            $this->component,
            $this->request,
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

        $model = new \DewdropTest\Model\Animals($this->db);
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
}
