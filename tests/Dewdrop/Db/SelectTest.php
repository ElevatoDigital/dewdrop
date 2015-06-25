<?php

namespace Dewdrop\Db;

use Dewdrop\Db\Adapter\Mock;
use Dewdrop\Db\Expr;
use Dewdrop\Db\Select\SelectException;
use Dewdrop\Test\BaseTestCase;

class SelectTest extends BaseTestCase
{
    protected $db = null;

    /**
     * Subclasses should call parent::setUp() before
     * doing their own logic, e.g. creating metadata.
     */
    public function setUp()
    {
        $this->setUpAdapter();
    }

    /**
     * Open a new database connection
     */
    protected function setUpAdapter()
    {
        $this->db = $this->getMock(
            '\Dewdrop\Db\Adapter',
            array('query'),
            array()
        );

        $driver = new \Dewdrop\Db\Driver\Mock($this->db);

        $this->db->setDriver($driver);
    }

    /**
     * Subclasses should call parent::tearDown() after
     * doing their own logic, e.g. deleting metadata.
     */
    public function tearDown()
    {
        $this->db = null;
    }

    /**
     * Test basic use of the \Dewdrop\Db\Select class.
     *
     * @return \Dewdrop\Db\Select
     */
    protected function select()
    {
        $select = $this->db->select();
        $select->from('zfproducts');
        return $select;
    }

    public function testSelect()
    {
        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts`',
            (string) $this->select()
        );
    }

    public function testSelectToString()
    {
        $select = $this->select();
        $this->assertEquals($select->__toString(), $select->assemble()); // correct data
    }

    /**
     * ZF-2017: Test bind use of the \Dewdrop\Db\Select class.
     * @group ZF-2017
     */
    public function testSelectQueryWithBinds()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->select()->where("$product_id = :product_id")
                                  ->bind(array(':product_id' => 1));

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = :product_id)',
            (string) $select
        );
    }

    /**
     * Test \Dewdrop\Db\Select specifying columns
     */
    protected function selectColumnsScalar()
    {
        $select = $this->db->select()
            ->from('zfproducts', 'product_name'); // scalar
        return $select;
    }

    protected function selectColumnsArray()
    {
        $select = $this->db->select()
            ->from('zfproducts', array('product_id', 'product_name')); // array
        return $select;
    }

    public function testSelectColumnsArray()
    {
        $select = $this->selectColumnsArray();

        $this->assertEquals(
            'SELECT `zfproducts`.`product_id`, `zfproducts`.`product_name` FROM `zfproducts`',
            (string) $select
        );
    }

    /**
     * Test support for column aliases.
     * e.g. from('table', array('alias' => 'col1')).
     */
    protected function selectColumnsAliases()
    {
        $select = $this->db->select()
            ->from('zfproducts', array('alias' => 'product_name'));
        return $select;
    }

    public function testSelectColumnsAliases()
    {
        $select = $this->selectColumnsAliases();

        $this->assertEquals(
            'SELECT `zfproducts`.`product_name` AS `alias` FROM `zfproducts`',
            (string) $select
        );
    }

    /**
     * Test syntax to support qualified column names,
     * e.g. from('table', array('table.col1', 'table.col2')).
     */
    protected function selectColumnsQualified()
    {
        $select = $this->db->select()
            ->from('zfproducts', "zfproducts.product_name");
        return $select;
    }

    public function testSelectColumnsQualified()
    {
        $select = $this->selectColumnsQualified();

        $this->assertEquals(
            'SELECT `zfproducts`.`product_name` FROM `zfproducts`',
            (string) $select
        );
    }

    /**
     * Test support for columns defined by \Dewdrop\Db\Expr.
     */
    protected function selectColumnsExpr()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $product_name = $this->db->quoteIdentifier('product_name');

        $select = $this->db->select()
            ->from('zfproducts', new \Dewdrop\Db\Expr($products.'.'.$product_name));
        return $select;
    }

    public function testSelectColumnsExpr()
    {
        $select = $this->selectColumnsExpr();

        $this->assertEquals(
            'SELECT `zfproducts`.`product_name` FROM `zfproducts`',
            (string) $select
        );
    }

    /**
     * Test support for automatic conversion of SQL functions to
     * \Dewdrop\Db\Expr, e.g. from('table', array('COUNT(*)'))
     * should generate the same result as
     * from('table', array(new \Dewdrop\Db\Expr('COUNT(*)')))
     */
    protected function selectColumnsAutoExpr()
    {
        $select = $this->db->select()
            ->from('zfproducts', array('count' => 'COUNT(*)'));
        return $select;
    }

    public function testSelectColumnsAutoExpr()
    {
        $select = $this->selectColumnsAutoExpr();

        $this->assertEquals(
            'SELECT COUNT(*) AS `count` FROM `zfproducts`',
            (string) $select
        );
    }

    /**
     * Test adding the DISTINCT query modifier to a \Dewdrop\Db\Select object.
     */
    protected function selectDistinctModifier()
    {
        $select = $this->db->select()
            ->distinct()
            ->from('zfproducts', new \Dewdrop\Db\Expr(327));
        return $select;
    }

    public function testSelectDistinctModifier()
    {
        $select = $this->selectDistinctModifier();

        $this->assertEquals(
            'SELECT DISTINCT 327 FROM `zfproducts`',
            (string) $select
        );
    }

    /**
     * Test support for schema-qualified table names in from()
     * e.g. from('schema.table').
     */
    protected function selectFromQualified()
    {
        $select = $this->db->select()
            ->from("public.zfproducts");
        return $select;
    }

    public function testSelectFromQualified()
    {
        $select = $this->selectFromQualified();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `public`.`zfproducts`',
            (string) $select
        );
    }

    /**
     * Test support for nested select in from()
     */
    protected function selectFromSelectObject()
    {
        $subquery = $this->db->select()
            ->from('subqueryTable');

        $select = $this->db->select()
            ->from($subquery);
        return $select;
    }

    public function testSelectFromSelectObject()
    {
        $select = $this->selectFromSelectObject();
        $query = $select->assemble();
        $cmp = 'SELECT ' . $this->db->quoteIdentifier('t') . '.* FROM (SELECT '
                         . $this->db->quoteIdentifier('subqueryTable') . '.* FROM '
                         . $this->db->quoteIdentifier('subqueryTable') . ') AS '
                         . $this->db->quoteIdentifier('t');
        $this->assertEquals($query, $cmp);
    }

    /**
     * Test support for nested select in from()
     */
    protected function selectColumnsReset()
    {
        $select = $this->db->select()
            ->from(array('p' => 'zfproducts'), array('product_id', 'product_name'));
        return $select;
    }

    public function testSelectColumnsReset()
    {
        $select = $this->selectColumnsReset()
            ->reset(\Dewdrop\Db\Select::COLUMNS)
            ->columns('product_name');
        $this->assertContains('product_name', (string) $select);
        $this->assertNotContains('product_id', (string) $select);

        $select = $this->selectColumnsReset()
            ->reset(\Dewdrop\Db\Select::COLUMNS)
            ->columns('p.product_name');
        $this->assertContains('product_name', (string) $select);
        $this->assertNotContains('product_id', (string) $select);

        $select = $this->selectColumnsReset()
            ->reset(\Dewdrop\Db\Select::COLUMNS)
            ->columns('product_name', 'p');
        $this->assertContains('product_name', (string) $select);
        $this->assertNotContains('product_id', (string) $select);
    }

    public function testSelectColumnsResetBeforeFrom()
    {
        $select = $this->selectColumnsReset();
        try {
            $select->reset(\Dewdrop\Db\Select::COLUMNS)
                   ->reset(\Dewdrop\Db\Select::FROM)
                   ->columns('product_id');
            $this->fail('Expected exception of type "\Dewdrop\Db\Select\SelectException"');
        } catch (\Dewdrop\Db\Select\SelectException $e) {
            $this->assertEquals("No table has been specified for the FROM clause", $e->getMessage());
        }
    }

    protected function selectColumnWithColonQuotedParameter()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where($product_id . ' = ?', "as'as:x");
        return $select;
    }

    public function testSelectColumnWithColonQuotedParameter()
    {
        $select = $this->selectColumnWithColonQuotedParameter();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 'as\'as:x')",
            (string) $select
        );
    }

    /**
     * Test support for FOR UPDATE
     * e.g. from('schema.table').
     */
    public function testSelectFromForUpdate()
    {
        $select = $this->db->select()
            ->from("zfproducts")
            ->forUpdate();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` FOR UPDATE',
            (string) $select
        );
    }

    /**
     * Test adding a JOIN to a \Dewdrop\Db\Select object.
     */
    protected function selectJoin()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $product_id = $this->db->quoteIdentifier('product_id');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');

        $select = $this->db->select()
            ->from('zfproducts')
            ->join('zfbugs_products', "$products.$product_id = $bugs_products.$product_id");
        return $select;
    }

    public function testSelectJoin()
    {
        $select = $this->selectJoin();

        $this->assertEquals(
            "SELECT `zfproducts`.*, `zfbugs_products`.* FROM `zfproducts`\n INNER JOIN `zfbugs_products` ON `zfproducts`.`product_id` = `zfbugs_products`.`product_id`",
            (string) $select
        );
    }

    /**
     * Test adding an INNER JOIN to a \Dewdrop\Db\Select object.
     * This should be exactly the same as the plain JOIN clause.
     */
    protected function selectJoinWithCorrelationName()
    {
        $product_id = $this->db->quoteIdentifier('product_id');
        $xyz1 = $this->db->quoteIdentifier('xyz1');
        $xyz2 = $this->db->quoteIdentifier('xyz2');

        $select = $this->db->select()
            ->from( array('xyz1' => 'zfproducts') )
            ->join( array('xyz2' => 'zfbugs_products'), "$xyz1.$product_id = $xyz2.$product_id")
            ->where("$xyz1.$product_id = 1");
        return $select;
    }

    public function testSelectJoinWithCorrelationName()
    {
        $select = $this->selectJoinWithCorrelationName();

        $this->assertEquals(
            "SELECT `xyz1`.*, `xyz2`.* FROM `zfproducts` AS `xyz1`
 INNER JOIN `zfbugs_products` AS `xyz2` ON `xyz1`.`product_id` = `xyz2`.`product_id` WHERE (`xyz1`.`product_id` = 1)",
            (string) $select
        );
    }

    /**
     * Test adding an INNER JOIN to a \Dewdrop\Db\Select object.
     * This should be exactly the same as the plain JOIN clause.
     */
    protected function selectJoinInner()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $product_id = $this->db->quoteIdentifier('product_id');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');

        $select = $this->db->select()
            ->from('zfproducts')
            ->joinInner('zfbugs_products', "$products.$product_id = $bugs_products.$product_id");
        return $select;
    }

    public function testSelectJoinInner()
    {
        $select = $this->selectJoinInner();

        $this->assertEquals(
            "SELECT `zfproducts`.*, `zfbugs_products`.* FROM `zfproducts`
 INNER JOIN `zfbugs_products` ON `zfproducts`.`product_id` = `zfbugs_products`.`product_id`",
            (string) $select
        );
    }

    /**
     * Test adding a JOIN to a \Dewdrop\Db\Select object.
     */
    protected function selectJoinWithNocolumns()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $bug_id = $this->db->quoteIdentifier('bug_id');
        $product_id = $this->db->quoteIdentifier('product_id');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $bugs = $this->db->quoteIdentifier('zfbugs');

        $select = $this->db->select()
            ->from('zfproducts')
            ->join('zfbugs', "$bugs.$bug_id = 1", array())
            ->join('zfbugs_products', "$products.$product_id = $bugs_products.$product_id AND $bugs_products.$bug_id = $bugs.$bug_id", null);
        return $select;
    }

    public function testSelectJoinWithNocolumns()
    {
        $select = $this->selectJoinWithNocolumns();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` INNER JOIN `zfbugs` ON `zfbugs`.`bug_id` = 1 INNER JOIN `zfbugs_products` ON `zfproducts`.`product_id` = `zfbugs_products`.`product_id` AND `zfbugs_products`.`bug_id` = `zfbugs`.`bug_id`',
            str_replace(PHP_EOL, '', (string) $select)
        );
    }

    /**
     * Test adding an outer join to a \Dewdrop\Db\Select object.
     */
    protected function selectJoinLeft()
    {
        $bugs = $this->db->quoteIdentifier('zfbugs');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $bug_id = $this->db->quoteIdentifier('bug_id');

        $select = $this->db->select()
            ->from('zfbugs')
            ->joinLeft('zfbugs_products', "$bugs.$bug_id = $bugs_products.$bug_id");
        return $select;
    }

    public function testSelectJoinLeft()
    {
        $select = $this->selectJoinLeft();

        $this->assertEquals(
            'SELECT `zfbugs`.*, `zfbugs_products`.* FROM `zfbugs` LEFT JOIN `zfbugs_products` ON `zfbugs`.`bug_id` = `zfbugs_products`.`bug_id`',
            str_replace(PHP_EOL, '', (string) $select)
        );
    }

    /**
     * Returns a select object that uses table aliases and specifies a mixed ordering of columns,
     * for testing whether the user-specified ordering is preserved.
     *
     * @return \Dewdrop\Db\Select
     */
    protected function selectJoinLeftTableAliasesColumnOrderPreserve()
    {
        $bugsBugId        = $this->db->quoteIdentifier('b.bug_id');
        $bugsProductBugId = $this->db->quoteIdentifier('bp.bug_id');

        $select = $this->db->select()
            ->from(array('b' => 'zfbugs'), array('b.bug_id', 'bp.product_id', 'b.bug_description'))
            ->joinLeft(array('bp' => 'zfbugs_products'), "$bugsBugId = $bugsProductBugId", array());

        return $select;
    }

    /**
     * Ensures that when table aliases are used with a mixed ordering of columns, the user-specified
     * column ordering is preserved.
     *
     * @return void
     */
    public function testJoinLeftTableAliasesColumnOrderPreserve()
    {
        $select = $this->selectJoinLeftTableAliasesColumnOrderPreserve();
        $this->assertRegExp('/^.*b.*bug_id.*,.*bp.*product_id.*,.*b.*bug_description.*$/s', $select->assemble());
    }

    /**
     * Test adding an outer join to a \Dewdrop\Db\Select object.
     */
    protected function selectJoinRight()
    {
        $bugs = $this->db->quoteIdentifier('zfbugs');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $bug_id = $this->db->quoteIdentifier('bug_id');

        $select = $this->db->select()
            ->from('zfbugs_products')
            ->joinRight('zfbugs', "$bugs_products.$bug_id = $bugs.$bug_id");
        return $select;
    }

    public function testSelectJoinRight()
    {
        $select = $this->selectJoinRight();

        $this->assertEquals(
            'SELECT `zfbugs_products`.*, `zfbugs`.* FROM `zfbugs_products` RIGHT JOIN `zfbugs` ON `zfbugs_products`.`bug_id` = `zfbugs`.`bug_id`',
            str_replace(PHP_EOL, '', (string) $select)
        );
    }

    /**
     * Test adding a cross join to a \Dewdrop\Db\Select object.
     */
    protected function selectJoinCross()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->joinCross('zfbugs_products');
        return $select;
    }

    public function testSelectJoinCross()
    {
        $select = $this->selectJoinCross();

        $this->assertEquals(
            'SELECT `zfproducts`.*, `zfbugs_products`.* FROM `zfproducts` CROSS JOIN `zfbugs_products`',
            str_replace(PHP_EOL, '', (string) $select)
        );
    }

    /**
     * Test support for schema-qualified table names in join(),
     * e.g. join('schema.table', 'condition')
     */
    protected function selectJoinQualified()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $product_id = $this->db->quoteIdentifier('product_id');

        $schema = 'zfexample';

        $select = $this->db->select()
            ->from('zfproducts')
            ->join("$schema.zfbugs_products", "$products.$product_id = $bugs_products.$product_id");
        return $select;
    }

    public function testSelectJoinQualified()
    {
        $select = $this->selectJoinQualified();

        $this->assertEquals(
            'SELECT `zfproducts`.*, `zfbugs_products`.* FROM `zfproducts` INNER JOIN `zfexample`.`zfbugs_products` ON `zfproducts`.`product_id` = `zfbugs_products`.`product_id`',
            str_replace(PHP_EOL, '', $select)
        );
    }

    protected function selectJoinUsing()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->joinUsing("zfbugs_products", "$product_id")
            ->where("$bugs_products.$product_id < ?", 3);
        return $select;
    }

    public function testSelectMagicMethod()
    {
        $select = $this->selectJoinUsing();
        try {
            $select->foo();
            $this->fail('Expected exception of type "\Dewdrop\Db\Select\SelectException"');
        } catch (\Dewdrop\Db\Select\SelectException $e) {
            $this->assertEquals("Unrecognized method 'foo()'", $e->getMessage());
        }
    }

    public function testSelectJoinUsing()
    {
        $select = $this->selectJoinUsing();

        $this->assertEquals(
            'SELECT `zfproducts`.*, ```zfbugs_products``.``product_id`` = ``zfproducts```.```product_id``` FROM `zfproducts` INNER JOIN `*`.`inner join` ON zfbugs_products WHERE (`zfbugs_products`.`product_id` < 3)',
            str_replace(PHP_EOL, '', $select)
        );
    }

    protected function selectJoinInnerUsing()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->joinInnerUsing("zfbugs_products", "$product_id")
            ->where("$bugs_products.$product_id < ?", 3);
        return $select;
    }

    public function testSelectJoinInnerUsing()
    {
        $select = $this->selectJoinInnerUsing();

        $this->assertEquals(
            'SELECT `zfproducts`.*, ```zfbugs_products``.``product_id`` = ``zfproducts```.```product_id``` FROM `zfproducts` INNER JOIN `*`.`inner join` ON zfbugs_products WHERE (`zfbugs_products`.`product_id` < 3)',
            str_replace(PHP_EOL, '', $select)
        );
    }

    public function testSelectJoinInnerUsingException()
    {
        $select = $this->selectJoinInnerUsing();
        try {
            $select->joinFooUsing();
            $this->fail('Expected exception of type "\Dewdrop\Db\Select\SelectException"');
        } catch (\Dewdrop\Db\Select\SelectException $e) {
            $this->assertEquals("Unrecognized method 'joinFooUsing()'", $e->getMessage());
        }
    }

    protected function selectJoinCrossUsing()
    {
        $products = $this->db->quoteIdentifier('zfproducts');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where("$bugs_products.$product_id < ?", 3);
        return $select;
    }

    public function testSelectJoinCrossUsing()
    {
        $product_id = $this->db->quoteIdentifier('product_id');
        $select = $this->selectJoinCrossUsing();
        try {
            $select->joinCrossUsing("zfbugs_products", "$product_id");
            $this->fail('Expected exception of type "\Dewdrop\Db\Select\SelectException"');
        } catch (\Dewdrop\Db\Select\SelectException $e) {
            $this->assertEquals("Cannot perform a joinUsing with method 'joinCrossUsing()'", $e->getMessage());
        }
    }

    /**
     * Test adding a WHERE clause to a \Dewdrop\Db\Select object.
     */
    protected function selectWhere()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where("$product_id = 2");
        return $select;
    }

    public function testSelectWhere()
    {
        $select = $this->selectWhere();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 2)',
            (string) $select
        );
    }

    /**
     * Test support for nested select in from()
     */
    protected function selectWhereSelectObject()
    {
        $subquery = $this->db->select()
            ->from('subqueryTable');

        $select = $this->db->select()
            ->from('table')
            ->where('foo IN ?', $subquery);
        return $select;
    }

    public function testSelectWhereSelectObject()
    {
        $select = $this->selectWhereSelectObject();
        $query = $select->assemble();
        $cmp = 'SELECT ' . $this->db->quoteIdentifier('table') . '.* FROM '
                         . $this->db->quoteIdentifier('table') . ' WHERE (foo IN (SELECT '
                         . $this->db->quoteIdentifier('subqueryTable') . '.* FROM '
                         . $this->db->quoteIdentifier('subqueryTable') . '))';
        $this->assertEquals($query, $cmp);
    }

    protected function selectWhereArray()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where("$product_id IN (?)", array(1, 2, 3));
        return $select;
    }

    public function testSelectWhereArray()
    {
        $select = $this->selectWhereArray();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` IN (1, 2, 3))',
            (string) $select
        );
    }

    /**
     * test adding more WHERE conditions,
     * which should be combined with AND by default.
     */
    protected function selectWhereAnd()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where("$product_id = 2")
            ->where("$product_id = 1");
        return $select;
    }

    public function testSelectWhereAnd()
    {
        $select = $this->selectWhereAnd();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 2) AND (`product_id` = 1)',
            $select->__toString()
        );
    }

    /**
     * Test support for where() with a parameter,
     * e.g. where('id = ?', 1).
     */
    protected function selectWhereWithParameter()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where("$product_id = ?", 2);
        return $select;
    }

    public function testSelectWhereWithParameter()
    {
        $select = $this->selectWhereWithParameter();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 2)",
            (string) $select
        );
    }

    /**
     * Test support for where() with a specified type,
     * e.g. where('id = ?', 1, 'int').
     */
    protected function selectWhereWithType()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->where("$product_id = ?", 2, 'int');
        return $select;
    }

    public function testSelectWhereWithType()
    {
        $select = $this->selectWhereWithType();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 2)",
            (string) $select
        );
    }

    /**
     * Test support for where() with a specified type,
     * e.g. where('id = ?', 1, 'int').
     */
    protected function selectWhereWithTypeFloat()
    {
        $price_total = $this->db->quoteIdentifier('price_total');

        $select = $this->db->select()
            ->from('zfprice')
            ->where("$price_total = ?", 200.45, \Dewdrop\Db\Adapter::FLOAT_TYPE);
        return $select;
    }

    public function testSelectWhereWithTypeFloat()
    {
        $select = $this->selectWhereWithTypeFloat();

        $this->assertEquals(
            'SELECT `zfprice`.* FROM `zfprice` WHERE (`price_total` = 200.450000)',
            (string) $select
        );
    }

    /**
     * Test adding an OR WHERE clause to a \Dewdrop\Db\Select object.
     */
    protected function selectWhereOr()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->orWhere("$product_id = 1")
            ->orWhere("$product_id = 2");
        return $select;
    }

    public function testSelectWhereOr()
    {
        $select = $this->selectWhereOr();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 1) OR (`product_id` = 2)",
            (string) $select
        );
    }

    /**
     * Test support for where() with a parameter,
     * e.g. orWhere('id = ?', 2).
     */
    protected function selectWhereOrWithParameter()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts')
            ->orWhere("$product_id = ?", 1)
            ->orWhere("$product_id = ?", 2);
        return $select;
    }

    public function testSelectWhereOrWithParameter()
    {
        $select = $this->selectWhereOrWithParameter();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` WHERE (`product_id` = 1) OR (`product_id` = 2)",
            (string) $select
        );
    }

    /**
     * Test adding a GROUP BY clause to a \Dewdrop\Db\Select object.
     */
    protected function selectGroupBy()
    {
        $thecount = $this->db->quoteIdentifier('thecount');

        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', new \Dewdrop\Db\Expr("COUNT(*) AS $thecount")))
            ->group('bug_id')
            ->order('bug_id');
        return $select;
    }

    public function testSelectGroupBy()
    {
        $select = $this->selectGroupBy();

        $this->assertEquals(
            "SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id` ORDER BY `bug_id` ASC",
            (string) $select
        );
    }

    /**
     * Test support for qualified table in group(),
     * e.g. group('schema.table').
     */
    protected function selectGroupByQualified()
    {
        $thecount = $this->db->quoteIdentifier('thecount');

        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', new \Dewdrop\Db\Expr("COUNT(*) AS $thecount")))
            ->group("zfbugs_products.bug_id")
            ->order('bug_id');
        return $select;
    }

    public function testSelectGroupByQualified()
    {
        $select = $this->selectGroupByQualified();

        $this->assertEquals(
            "SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `zfbugs_products`.`bug_id` ORDER BY `bug_id` ASC",
            (string) $select
        );
    }

    /**
     * Test support for \Dewdrop\Db\Expr in group(),
     * e.g. group(new \Dewdrop\Db\Expr('id+1'))
     */
    protected function selectGroupByExpr()
    {
        $thecount = $this->db->quoteIdentifier('thecount');
        $bug_id = $this->db->quoteIdentifier('bug_id');

        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id'=>new \Dewdrop\Db\Expr("$bug_id+1"), new \Dewdrop\Db\Expr("COUNT(*) AS $thecount")))
            ->group(new \Dewdrop\Db\Expr("$bug_id+1"))
            ->order(new \Dewdrop\Db\Expr("$bug_id+1"));
        return $select;
    }

    public function testSelectGroupByExpr()
    {
        $select = $this->selectGroupByExpr();

        $this->assertEquals(
            "SELECT `bug_id`+1 AS `bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id`+1 ORDER BY `bug_id`+1",
            (string) $select
        );
    }

    /**
     * Test support for automatic conversion of a SQL
     * function to a \Dewdrop\Db\Expr in group(),
     * e.g.  group('LOWER(title)') should give the same
     * result as group(new \Dewdrop\Db\Expr('LOWER(title)')).
     */

    protected function selectGroupByAutoExpr()
    {
        $thecount = $this->db->quoteIdentifier('thecount');
        $bugs_products = $this->db->quoteIdentifier('zfbugs_products');
        $bug_id = $this->db->quoteIdentifier('bug_id');

        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id'=>"ABS($bugs_products.$bug_id)", new \Dewdrop\Db\Expr("COUNT(*) AS $thecount")))
            ->group("ABS($bugs_products.$bug_id)");
        return $select;
    }

    public function testSelectGroupByAutoExpr()
    {
        $select = $this->selectGroupByAutoExpr();

        $this->assertEquals(
            "SELECT ABS(`zfbugs_products`.`bug_id`) AS `bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY ABS(`zfbugs_products`.`bug_id`)",
            (string) $select
        );
    }

    /**
     * Test adding a HAVING clause to a \Dewdrop\Db\Select object.
     */
    protected function selectHaving()
    {
        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', 'COUNT(*) AS thecount'))
            ->group('bug_id')
            ->having('COUNT(*) > 1')
            ->order('bug_id');
        return $select;
    }

    public function testSelectHaving()
    {
        $select = $this->selectHaving();

        $this->assertEquals(
            "SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id` HAVING (COUNT(*) > 1) ORDER BY `bug_id` ASC",
            (string) $select
        );
    }

    protected function selectHavingAnd()
    {
        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', 'COUNT(*) AS thecount'))
            ->group('bug_id')
            ->having('COUNT(*) > 1')
            ->having('COUNT(*) = 1')
            ->order('bug_id');
        return $select;
    }

    public function testSelectHavingAnd()
    {
        $select = $this->selectHavingAnd();

        $this->assertEquals(
            "SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id` HAVING (COUNT(*) > 1) AND (COUNT(*) = 1) ORDER BY `bug_id` ASC",
            (string) $select
        );
    }

    /**
     * Test support for parameter in having(),
     * e.g. having('count(*) > ?', 1).
     */

    protected function selectHavingWithParameter()
    {
        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', 'COUNT(*) AS thecount'))
            ->group('bug_id')
            ->having('COUNT(*) > ?', 1)
            ->order('bug_id');
        return $select;
    }

    public function testSelectHavingWithParameter()
    {
        $select = $this->selectHavingWithParameter();

        $this->assertEquals(
            "SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id` HAVING (COUNT(*) > 1) ORDER BY `bug_id` ASC",
            (string) $select
        );
    }

    /**
     * Test adding a HAVING clause to a \Dewdrop\Db\Select object.
     */

    protected function selectHavingOr()
    {
        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', 'COUNT(*) AS thecount'))
            ->group('bug_id')
            ->orHaving('COUNT(*) > 1')
            ->orHaving('COUNT(*) = 1')
            ->order('bug_id');
        return $select;
    }

    public function testSelectHavingOr()
    {
        $select = $this->selectHavingOr();

        $this->assertEquals(
            'SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id` HAVING (COUNT(*) > 1) OR (COUNT(*) = 1) ORDER BY `bug_id` ASC',
            (string) $select
        );
    }

    /**
     * Test support for parameter in orHaving(),
     * e.g. orHaving('count(*) > ?', 1).
     */
    protected function selectHavingOrWithParameter()
    {
        $select = $this->db->select()
            ->from('zfbugs_products', array('bug_id', 'COUNT(*) AS thecount'))
            ->group('bug_id')
            ->orHaving('COUNT(*) > ?', 1)
            ->orHaving('COUNT(*) = ?', 1)
            ->order('bug_id');
        return $select;
    }

    public function testSelectHavingOrWithParameter()
    {
        $select = $this->selectHavingOrWithParameter();

        $this->assertEquals(
            "SELECT `zfbugs_products`.`bug_id`, COUNT(*) AS `thecount` FROM `zfbugs_products` GROUP BY `bug_id` HAVING (COUNT(*) > 1) OR (COUNT(*) = 1) ORDER BY `bug_id` ASC",
            (string) $select
        );
    }

    /**
     * Test adding an ORDER BY clause to a \Dewdrop\Db\Select object.
     */
    protected function selectOrderBy()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('product_id');
        return $select;
    }

    public function testSelectOrderBy()
    {
        $select = $this->selectOrderBy();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC",
            (string) $select
        );
    }

    protected function selectOrderByArray()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order(array('product_name', 'product_id'));
        return $select;
    }

    public function testSelectOrderByArray()
    {
        $select = $this->selectOrderByArray();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_name` ASC, `product_id` ASC",
            (string) $select
        );
    }

    protected function selectOrderByAsc()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order("product_id ASC");
        return $select;
    }

    public function testSelectOrderByAsc()
    {
        $select = $this->selectOrderByAsc();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC",
            (string) $select
        );
    }

    protected function selectOrderByPosition()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('2');
        return $select;
    }

    public function testSelectOrderByPosition()
    {
        $select = $this->selectOrderByPosition();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` ORDER BY 2 ASC",
            (string) $select
        );
    }

    protected function selectOrderByPositionAsc()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('2 ASC');
        return $select;
    }

    public function testSelectOrderByPositionAsc()
    {
        $select = $this->selectOrderByPositionAsc();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY 2 ASC',
            $select->__toString()
        );
    }

    protected function selectOrderByPositionDesc()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('2 DESC');
        return $select;
    }

    public function testSelectOrderByPositionDesc()
    {
        $select = $this->selectOrderByPositionDesc();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY 2 DESC',
            $select->__toString()
        );
    }

    protected function selectOrderByMultiplePositions()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order(array('2 DESC', '1 DESC'));
        return $select;
    }

    public function testSelectOrderByMultiplePositions()
    {
        $select = $this->selectOrderByMultiplePositions();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY 2 DESC, 1 DESC',
            $select->__toString()
        );
    }

    protected function selectOrderByDesc()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order("product_id DESC");
        return $select;
    }

    public function testSelectOrderByDesc()
    {
        $select = $this->selectOrderByDesc();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` DESC',
            $select->__toString()
        );
    }

    /**
     * Test support for qualified table in order(),
     * e.g. order('schema.table').
     */
    protected function selectOrderByQualified()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order("zfproducts.product_id");
        return $select;
    }

    public function testSelectOrderByQualified()
    {
        $select = $this->selectOrderByQualified();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `zfproducts`.`product_id` ASC',
            $select->__toString()
        );
    }

    /**
     * Test support for \Dewdrop\Db\Expr in order(),
     * e.g. order(new \Dewdrop\Db\Expr('id+1')).
     */
    protected function selectOrderByExpr()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order(new \Dewdrop\Db\Expr("1"));
        return $select;
    }

    public function testSelectOrderByExpr()
    {
        $select = $this->selectOrderByExpr();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY 1',
            $select->__toString()
        );
    }

    /**
     * Test ORDER BY clause that contains multiple lines.
     * See ZF-1822, which says that the regexp matching
     * ASC|DESC fails when string is multi-line.
     */
    protected function selectOrderByMultiLine()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order("product_id\nDESC");
        return $select;
    }

    public function testSelectOrderByMultiLine()
    {
        $select = $this->selectOrderByMultiLine();

        $this->assertEquals(
            "SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` DESC",
            $select->__toString()
        );
    }

    /**
     * @group ZF-4246
     */
    protected function _checkExtraField($result)
    {
        // Check that extra field ZENDdb_ROWNUM isn't present
        // (particulary with Db2 & Oracle)
        $this->assertArrayNotHasKey('zenddb_rownum', $result);
        $this->assertArrayNotHasKey('ZENDdb_ROWNUM', $result);
    }

    /**
     * Test adding a LIMIT clause to a \Dewdrop\Db\Select object.
     */
    protected function selectLimit()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('product_id')
            ->limit(1);
        return $select;
    }

    /**
     * @group ZF-4246
     */
    public function testSelectLimit()
    {
        $select = $this->selectLimit();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC LIMIT 1',
            $select->__toString()
        );
    }

    /**
     * @group ZF-5263
     * @group ZF-4246
     */
    public function testSelectLimitFetchCol()
    {
        $product_id = $this->db->quoteIdentifier('product_id');

        $select = $this->db->select()
            ->from('zfproducts', 'product_name')
            ->where($product_id . ' = ?', 3)
            ->limit(1);

        $this->assertEquals(
            'SELECT `zfproducts`.`product_name` FROM `zfproducts` WHERE (`product_id` = 3) LIMIT 1',
            (string) $select
        );
    }

    protected function selectLimitNone()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('product_id')
            ->limit(); // no limit
        return $select;
    }

    /**
     * @group ZF-4246
     */
    public function testSelectLimitNone()
    {
        $select = $this->selectLimitNone();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC',
            $select->__toString()
        );
    }

    protected function selectLimitOffset()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('product_id')
            ->limit(1, 1);
        return $select;
    }

    /**
     * @group ZF-4246
     */
    public function testSelectLimitOffset()
    {
        $select = $this->selectLimitOffset();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC LIMIT 1 OFFSET 1',
            $select->__toString()
        );
    }

    /**
     * Test the limitPage() method of a \Dewdrop\Db\Select object.
     */
    protected function selectLimitPageOne()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('product_id')
            ->limitPage(1, 1); // first page, length 1
        return $select;
    }

    /**
     * @group ZF-4246
     */
    public function testSelectLimitPageOne()
    {
        $select = $this->selectLimitPageOne();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC LIMIT 1',
            $select->__toString()
        );
    }

    protected function selectLimitPageTwo()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->order('product_id')
            ->limitPage(2, 1); // second page, length 1
        return $select;
    }

    /**
     * @group ZF-4246
     */
    public function testSelectLimitPageTwo()
    {
        $select = $this->selectLimitPageTwo();

        $this->assertEquals(
            'SELECT `zfproducts`.* FROM `zfproducts` ORDER BY `product_id` ASC LIMIT 1 OFFSET 1',
            $select->__toString()
        );
    }

    /**
     * Test the getPart() and reset() methods of a \Dewdrop\Db\Select object.
     */
    public function testSelectGetPartAndReset()
    {
        $select = $this->db->select()
            ->from('zfproducts')
            ->limit(1);
        $count = $select->getPart(\Dewdrop\Db\Select::LIMIT_COUNT);
        $this->assertEquals(1, $count);

        $select->reset(\Dewdrop\Db\Select::LIMIT_COUNT);
        $count = $select->getPart(\Dewdrop\Db\Select::LIMIT_COUNT);
        $this->assertNull($count);

        $select->reset(); // reset the whole object
        $from = $select->getPart(\Dewdrop\Db\Select::FROM);
        $this->assertTrue(empty($from));
    }

    /**
     * Test the UNION statement for a \Dewdrop\Db\Select object.
     */
    protected function selectUnionString()
    {
        $bugs = $this->db->quoteIdentifier('zfbugs');
        $bug_id = $this->db->quoteIdentifier('bug_id');
        $bug_status = $this->db->quoteIdentifier('bug_status');
        $products = $this->db->quoteIdentifier('zfproducts');
        $product_id = $this->db->quoteIdentifier('product_id');
        $product_name = $this->db->quoteIdentifier('product_name');
        $id = $this->db->quoteIdentifier('id');
        $name = $this->db->quoteIdentifier('name');
        $sql1 = "SELECT $bug_id AS $id, $bug_status AS $name FROM $bugs";
        $sql2 = "SELECT $product_id AS $id, $product_name AS $name FROM $products";

        $select = $this->db->select()
            ->union(array($sql1, $sql2))
            ->order('id');
        return $select;
    }

    public function testSelectUnionString()
    {
        $select = $this->selectUnionString();

        $this->assertEquals(
            'SELECT `bug_id` AS `id`, `bug_status` AS `name` FROM `zfbugs` UNION SELECT `product_id` AS `id`, `product_name` AS `name` FROM `zfproducts` ORDER BY `id` ASC',
            $select->__toString()
        );
    }

    /**
     * @group ZF-4772
     * @expectedException \Dewdrop\Db\Select\SelectException
     */
    public function testSelectUnionNoArrayThrowsException()
    {
        $this->db->select()->union('string');
    }

    /**
     * @group ZF-4772
     * @expectedException \Dewdrop\Db\Select\SelectException
     */
    public function testSelectUnionInvalidUnionTypeThrowsException()
    {
        $this->db->select()->union(array(), 'foo');
    }

    /**
     * @group ZF-6653
     */
    public function testSelectIsTheSameWhenCallingFromAndJoinInDifferentOrders()
    {
        $selectFromThenJoin = $this->db->select();
        $selectFromThenJoin->from(array('f' => 'foo'), array('columnfoo'))
            ->joinLeft(array('b' => 'bar'), 'f.columnfoo2 = b.barcolumn2', array('baralias' => 'barcolumn'));

        $selectJoinThenFrom = $this->db->select();
        $selectJoinThenFrom->joinLeft(array('b' => 'bar'), 'f.columnfoo2 = b.barcolumn2', array('baralias' => 'barcolumn'))
            ->from(array('f' => 'foo'), array('columnfoo'));

        $sqlSelectFromThenJoin = $selectFromThenJoin->assemble();
        $sqlSelectJoinThenFrom = $selectJoinThenFrom->assemble();
        $this->assertEquals($sqlSelectFromThenJoin, $sqlSelectJoinThenFrom);
    }

    /**
     * @group ZF-6653
     */
    public function testSelectIsTheSameWhenCallingMultipleFromsAfterJoin()
    {
        $selectFromThenJoin = $this->db->select();
        $selectFromThenJoin->from(array('f' => 'foo'), array('columnfoo'))
            ->from(array('d' => 'doo'), array('columndoo'))
            ->joinLeft(array('b' => 'bar'), 'f.columnfoo2 = b.barcolumn2', array('baralias' => 'barcolumn'));

        $selectJoinThenFrom = $this->db->select();
        $selectJoinThenFrom->joinLeft(array('b' => 'bar'), 'f.columnfoo2 = b.barcolumn2', array('baralias' => 'barcolumn'))
            ->from(array('f' => 'foo'), array('columnfoo'))
            ->from(array('d' => 'doo'), array('columndoo'));

        $sqlSelectFromThenJoin = $selectFromThenJoin->assemble();
        $sqlSelectJoinThenFrom = $selectJoinThenFrom->assemble();
        $this->assertEquals($sqlSelectFromThenJoin, $sqlSelectJoinThenFrom);
    }

    /**
     * @group ZF-6653
     */
    public function testSelectWithMultipleFromsAfterAJoinWillProperlyOrderColumns()
    {
        $select = $this->selectWithMultipleFromsAfterAJoinWillProperlyOrderColumns();
        $quote = $this->db->getQuoteIdentifierSymbol();
        $target = 'SELECT `f`.`columnfoo`, `d`.`columndoo`, `b`.`barcolumn` AS `baralias` FROM ' . $this->db->quoteTableAs('foo', 'f')
            . "\n" . ' INNER JOIN ' . $this->db->quoteTableAs('doo', 'd')
            . "\n" . ' LEFT JOIN ' . $this->db->quoteTableAs('bar', 'b') . ' ON f.columnfoo2 = b.barcolumn2';
        if ($quote != '`') {
            $target = str_replace('`', $quote, $target);
        }
        $this->assertEquals($target, $select->assemble());
    }

    protected function selectWithMultipleFromsAfterAJoinWillProperlyOrderColumns()
    {
        $selectJoinThenFrom = $this->db->select();
        $selectJoinThenFrom->joinLeft(array('b' => 'bar'), 'f.columnfoo2 = b.barcolumn2', array('baralias' => 'barcolumn'))
            ->from(array('f' => 'foo'), array('columnfoo'))
            ->from(array('d' => 'doo'), array('columndoo'));
        return $selectJoinThenFrom;
    }

    public function testSerializeSelect()
    {
        /* checks if the adapter has effectively gotten serialized,
           no exceptions are thrown here, so it's all right */
        $serialize = serialize($this->select());
        //$this->assertType('string',$serialize);
    }

    public function testGetAndSetBind()
    {
        static $bind = array('foo' => 'bar', 'baz' => 1);

        $select = $this->select();

        $this->assertSame(array(), $select->getBind());
        $this->assertSame($select, $select->bind($bind));
        $this->assertSame($bind, $select->getBind());
    }

    public function testJoinFull()
    {
        $select = $this->select()
            ->joinFull('zfbugs', '');

        $this->assertRegExp(
            '/^SELECT\s+`zfproducts`\.\*,\s+`zfbugs`\.\*\s+FROM\s+`zfproducts`\s+FULL\s+JOIN\s+`zfbugs`$/',
            $select->assemble()
        );
    }

    public function testJoinNatural()
    {
        $select = $this->select()
            ->joinNatural('zfbugs');

        $this->assertRegExp(
            '/^SELECT\s+`zfproducts`\.\*,\s+`zfbugs`\.\*\s+FROM\s+`zfproducts`\s+NATURAL\s+JOIN\s+`zfbugs`$/',
            $select->assemble()
        );
    }

    public function testOrder()
    {
        $select = $this->select()->order('');

        $this->assertRegExp(
            '/^SELECT\s+`zfproducts`\.\*\s+FROM\s+`zfproducts`$/',
            $select->assemble()
        );

        $select = $this->select()->order(new Expr(''));

        $this->assertRegExp(
            '/^SELECT\s+`zfproducts`\.\*\s+FROM\s+`zfproducts`$/',
            $select->assemble()
        );

        $select->order('foo');

        $this->assertRegExp(
            '/^SELECT\s+`zfproducts`\.\*\s+FROM\s+`zfproducts`\s+ORDER\s+BY\s+`foo`\s+ASC$/',
            $select->assemble()
        );
    }

    public function testGetPart()
    {
        $select = $this->select();

        $this->assertSame(
            array(
                'zfproducts' => array(
                    'joinType'      => 'from',
                    'schema'        => null,
                    'tableName'     => 'zfproducts',
                    'joinCondition' => null,
                ),
            ),
            $select->getPart($select::FROM)
        );

        try {
            $this->select()->getPart('foo');
        } catch (SelectException $e) {
            $this->assertContains('Invalid Select part \'foo\'', $e->getMessage());
        }
    }

    public function testQuery()
    {
        $select = $this->select();

        $this->db
            ->expects($this->once())
            ->method('query');

        $select->query();
    }

    public function testGetAdapter()
    {
        $this->assertSame($this->db, $this->select()->getAdapter());
    }

    public function testJoinUsingInternalException()
    {
        $select = new Select($this->db);

        try {
            $select->joinUsingInternal('type', 'name', 'cond');
        } catch (SelectException $e) {
            $this->assertSame('You can only perform a joinUsing after specifying a FROM table', $e->getMessage());
        }
    }
}
