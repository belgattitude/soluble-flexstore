<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 2016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace SolubleTest\FlexStore\Column;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\Formatter\CurrencyFormatter;
use Soluble\FlexStore\FlexStore;
use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\ColumnType;
use Soluble\FlexStore\Renderer\ClosureRenderer;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-10 at 15:15:20.
 */
class ColumnModelTest extends TestCase
{
    /**
     * @var SqlSource
     */
    protected $source;

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var ColumnModel
     */
    protected $columnModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new \Zend\Db\Sql\Select();
        $select->from('user');

        $this->source = new SqlSource($this->adapter, $select);

        $this->columnModel = $this->source->getColumnModel();
    }

    public function testRenderer()
    {
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();

        $f = function (\ArrayObject $row) {
            $row['product_id'] = 'My product id:' . $row['product_id'];
        };
        $clo = new ClosureRenderer($f);
        $cm->addRowRenderer($clo);

        $data = $store->getData();
        self::assertEquals('My product id:10', $data->current()->offsetGet('product_id'));
    }

    public function testRenderer2()
    {
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();
        $column = new Column('cool', ['type' => ColumnType::TYPE_STRING]);
        $cm->add($column);

        self::assertTrue($column->isVirtual());
        self::assertFalse($cm->get('product_id')->isVirtual());

        $f = function (\ArrayObject $row) {
            $row['cool'] = 'My cool value is :' . $row['product_id'];
        };
        $clo = new ClosureRenderer($f);
        $clo->setRequiredColumns(['product_id', 'reference']);
        $cm->addRowRenderer($clo);

        $data = $store->getData();
        self::assertEquals('My cool value is :10', $data->current()->offsetGet('cool'));
    }

    public function testRenderer3ThrowsException()
    {
        $this->expectException('Exception');
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();
        $column = new Column('cool', ['type' => ColumnType::TYPE_STRING]);
        $cm->add($column);

        $f2 = function (\ArrayObject $row) {
            if (!$row->offsetExists('pas_cool')) {
                throw new \Exception('pascool column in row');
            }
            $row['cool'] = 'My cool value is :' . $row['product_id'];
        };
        $clo = new ClosureRenderer($f2);
        $cm->addRowRenderer($clo);

        $data = $store->getData()->toArray();
    }

    public function testRendererThrowsMissingColumnException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\MissingColumnException');
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();
        $column = new Column('cool', ['type' => ColumnType::TYPE_STRING]);
        $cm->add($column);

        $f2 = function (\ArrayObject $row) {
            $row['cool'] = 'My cool value is :' . $row['product_id'];
        };
        $clo = new ClosureRenderer($f2);
        $clo->setRequiredColumns(['notexists']);
        $cm->addRowRenderer($clo);

        $data = $store->getData()->toArray();
    }

    public function testSearch()
    {
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();

        $results = $cm->search()->regexp('/price/');

        self::assertInstanceOf('Soluble\FlexStore\Column\ColumnModel\Search\Result', $results);
        self::assertEquals(['price', 'list_price', 'public_price'], $results->toArray());

        $formatterDb = new CurrencyFormatter([
                    'currency_code' => new \Soluble\FlexStore\Formatter\RowColumn('currency_reference')
        ]);
        self::assertInstanceOf(\Soluble\FlexStore\Formatter\RowColumn::class, $formatterDb->getCurrencyCode());
        $results->setFormatter($formatterDb);
        foreach ($results->toArray() as $name) {
            /**
             * @var CurrencyFormatter
             */
            $f = $cm->get($name)->getFormatter();
            self::assertEquals($formatterDb, $f);
            self::assertInstanceOf(\Soluble\FlexStore\Formatter\RowColumn::class, $f->getCurrencyCode());
        }

        $formatterEur = new CurrencyFormatter([
            'currency_code' => 'EUR'
        ]);

        self::assertEquals('EUR', $formatterEur->getCurrencyCode());

        $cm->get('price')->setFormatter($formatterEur);

        $test = $cm->search()->in(['price'])->toArray();
        self::assertEquals(['price'], $test);

        $cool = new Column('notincool');
        $cm->add($cool);
        $test = $cm->search()->notIn(['notincool'])->toArray();
        self::assertNotContains('notincool', $test);

        $cool = new Column('cooldate');
        $cool->setType('date');
        $cm->add($cool);
        $test = $cm->search()->findByType('date')->toArray();

        self::assertContains('cooldate', $test);

        $cool = new Column('cool');
        $cm->add($cool);
        $test = $cm->search()->in(['cool'])->toArray();
        self::assertEquals(['cool'], $test);

        $cool2 = new Column('cool2');
        $cm->add($cool2, 'cool');
        $test = $cm->search()->in(['cool2'])->toArray();
        self::assertEquals(['cool2'], $test);

        $cm->sort(['cool', 'cool2']);

        $cool3 = new Column('cool3');
        $cm->add($cool3);

        $test = $cm->search()->in(['cool3'])->toArray();
        self::assertEquals(['cool3'], $test);

        self::assertEquals($formatterEur, $cm->get('price')->getFormatter());
        self::assertEquals($formatterDb, $cm->get('list_price')->getFormatter());
    }

    public function testSetFormatter()
    {
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();

        $formatter = new CurrencyFormatter();
        $formatter->setLocale('fr_FR');
        $formatter->setCurrencyCode('EUR');

        $cm->get('price')->setFormatter($formatter);
        $data = $store->getData()->toArray();
        self::assertEquals('10,20 €', $data[0]['price']);
        // Null will be transformed in 0,00 €
        self::assertEquals('0,00 €', $data[3]['price']);

        $formatter->setLocale('en_US');
        $formatter->setCurrencyCode('USD');
        $cm->get('price')->setFormatter($formatter);
        $data = $store->getData()->toArray();
        self::assertEquals('$10.20', $data[0]['price']);
        // Null will be transformed in 0,00 €
        self::assertEquals('$0.00', $data[3]['price']);

        // store 2
        $store = new FlexStore($source);

        $formatter = new CurrencyFormatter();
        $formatter->setLocale('fr_FR');
        $formatter->setCurrencyCode('EUR');
        $cm = $store->getColumnModel();
        $cm->setFormatter($formatter, ['price', 'list_price']);
        $data = $store->getData()->toArray();
        self::assertEquals('10,20 €', $data[0]['price']);
        self::assertEquals('15,30 €', $data[0]['list_price']);
    }

    public function testCustomColumn()
    {
        $select = new Select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price')
        ]);

        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $cc = new Column('picture_url');
        $cc->setType('string');

        $cm->add($cc);
        self::assertTrue($cm->get($cc->getName())->isVirtual());
        $cm->sort(['picture_url', 'price', 'list_price']);
        $cm->exclude(['reference']);

        $fct = function (\ArrayObject $row) {
            $row['picture_url'] = 'http://' . $row['reference'];
        };
        $cm->addRowRenderer(new \Soluble\FlexStore\Renderer\ClosureRenderer($fct));

        $data = $source->getData()->toArray();
        $expected = [
            'picture_url' => 'http://TESTREF10',
            'price' => '10.200000',
            'list_price' => '15.300000',
            'product_id' => '10',
            'public_price' => '18.200000',
        ];
        self::assertEquals($expected, $data[0]);
    }

    public function testAddBeforeAndAfter()
    {
        $select = new Select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price')
        ]);

        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $cc = new Column('test');
        $cc->setType(ColumnType::TYPE_STRING);

        $cm->add($cc);
        try {
            $cm->add($cc);
            self::assertFalse(true, ' should throw DuplicateColumnException');
        } catch (\Soluble\FlexStore\Column\Exception\DuplicateColumnException $ex) {
            self::assertTrue(true);
        }

        // column must appear at the end
        $arr = array_keys((array) $cm->getColumns());
        self::assertEquals('test', $arr[count($arr) - 1]);

        // TEST INSERT AFTER
        $cc2 = new Column('insert_after');

        try {
            $cm->add($cc2, 'not_existentcolumn');
            self::assertFalse(true, ' should throw ColumnNotFoundException');
        } catch (\Soluble\FlexStore\Column\Exception\ColumnNotFoundException $ex) {
            self::assertTrue(true);
        }

        $cm->add($cc2, 'product_id');

        // column must appear at the end
        $arr = array_keys((array) $cm->getColumns());
        self::assertEquals('insert_after', $arr[1]);

        $cc2 = new Column('insert_after_end');
        $cm->add($cc2, 'test', ColumnModel::ADD_COLUMN_AFTER);
        $arr = array_keys((array) $cm->getColumns());
        self::assertEquals('insert_after_end', $arr[count($arr) - 1]);

        // TEST INSERT BEFORE
        $cc = new Column('insert_before');
        $cm->add($cc, 'product_id', ColumnModel::ADD_COLUMN_BEFORE);
        $arr = array_keys((array) $cm->getColumns());
        self::assertEquals('insert_before', $arr[0]);

        // TEST MODE EXCEPTION
        $cc = new Column('invalid_mode');
        try {
            $cm->add($cc, 'product_id', 'invalid_mode');
            self::assertFalse(true, ' should throw InvalidArgumentException');
        } catch (\Soluble\FlexStore\Column\Exception\InvalidArgumentException $ex) {
            self::assertTrue(true);
        }
    }

    public function testSomeInvalidArgumentException()
    {
        $select = new Select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price')
        ]);

        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        try {
            $cm->exists('');
            self::assertFalse(true, ' should throw InvalidArgumentException');
        } catch (\Soluble\FlexStore\Column\Exception\InvalidArgumentException $ex) {
            self::assertTrue(true);
        }

        try {
            $cm->sort(['product_id', 'undefined_col']);
            self::assertFalse(true, ' should throw InvalidArgumentException');
        } catch (\Soluble\FlexStore\Column\Exception\InvalidArgumentException $ex) {
            self::assertTrue(true);
        }
    }

    public function testAddRowRenderer()
    {
        $select = new Select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], new Expression('ppl.product_id = p.product_id and ppl.pricelist_id = 1'), [], $select::JOIN_LEFT);

        $select->columns([
            'product_id' => new Expression('p.product_id'),
            'reference' => new Expression('p.reference'),
            'price' => new Expression('ppl.price'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price')
        ]);

        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $fct = function (\ArrayObject $row) {
            $row['price'] = 200;
        };

        $fct2 = function (\ArrayObject $row) {
            if ($row['product_id'] == 113) {
                $row['reference'] = 'MyNEWREF';
            }
        };

        $cm->addRowRenderer(new \Soluble\FlexStore\Renderer\ClosureRenderer($fct));
        $cm->addRowRenderer(new \Soluble\FlexStore\Renderer\ClosureRenderer($fct2));

        $data = $source->getData()->toArray();
        foreach ($data as $row) {
            self::assertEquals(200, $row['price']);
            if ($row['product_id'] == 113) {
                self::assertEquals('MyNEWREF', $row['reference']);
            } else {
                self::assertNotEquals('MyNEWREF', $row['reference']);
            }
        }
    }

    public function testGetColumns()
    {
        $columnModel = $this->columnModel;
        self::assertInstanceOf('\Soluble\FlexStore\Column\ColumnModel', $columnModel);
        $columns = $columnModel->getColumns();
        self::assertInstanceOf('ArrayObject', $columns);
        foreach ($columns as $key => $column) {
            self::assertInstanceOf('Soluble\FlexStore\Column\Column', $column);
            self::assertEquals($key, $column->getName());
        }
    }

    public function testExclusion()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product');
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $excluded = ['product_id', 'legacy_mapping'];
        $cm->exclude($excluded);
        self::assertEquals($excluded, $cm->getExcluded());
    }

    public function testFindVirtual()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product');
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $excluded = ['product_id', 'legacy_mapping'];
        $cm->exclude($excluded);
        $cm->add(new Column('cool', $params = ['type' => 'string']));

        $virtual = $cm->search()->findVirtual()->toArray();
        self::assertEquals(['cool'], $virtual);
    }

    public function testSortColumns()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'password', 'email', 'username']);
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $sort = ['email', 'user_id'];
        $cm->sort($sort);

        self::assertEquals(['email', 'user_id', 'password', 'username'], array_keys((array) $cm->getColumns()));
    }

    public function testSortColumnsThrowsDuplicateColumnException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\DuplicateColumnException');
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'password', 'email', 'username']);
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $sort = ['email', 'user_id', 'email', 'user_id'];

        $cm->sort($sort);
    }

    public function testGetColumn()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'password', 'email', 'username']);
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();
        $col = $cm->get('user_id');
        self::assertInstanceOf('Soluble\FlexStore\Column\Column', $col);

        $select = new \Zend\Db\Sql\Select();
        $select->from('user');
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();
        $col = $cm->get('email');
        self::assertInstanceOf('Soluble\FlexStore\Column\Column', $col);
    }

    public function testHasColumn()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'password', 'username']);
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();
        self::assertTrue($cm->exists('user_id'));
        self::assertTrue($cm->exists('password'));
        self::assertFalse($cm->exists('email'));
    }

    public function testGetColumnThrowsColumnNotFoundException()
    {
        $this->expectException('Soluble\FlexStore\Column\Exception\ColumnNotFoundException');
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'password', 'username']);

        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();
        $cm->get('this_column_not_exists');
    }

    public function testIncludeOnly()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'email', 'displayName', 'username', 'password']);
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $include_only = ['email', 'user_id'];

        $cm->includeOnly($include_only);
        self::assertEquals($include_only, array_keys((array) $cm->getColumns()));
    }

    public function testIncludeOnlyWithSortAndExclusions()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'email', 'displayName', 'username', 'password']);
        $source = new SqlSource($this->adapter, $select);
        $cm = $source->getColumnModel();

        $include_only = ['email', 'user_id', 'username'];

        $cm->includeOnly($include_only, $sort = false);
        self::assertEquals(['user_id', 'email', 'username'], array_keys((array) $cm->getColumns()));

        $cm->exclude(['user_id']);
        $cm->includeOnly($include_only, $sort = true, $preserve_excluded = false);
        self::assertEquals(['email', 'user_id', 'username'], array_keys((array) $cm->getColumns()));

        $cm->exclude(['user_id', 'username']);
        $cm->includeOnly($include_only, $sort = true, $preserve_excluded = true);
        self::assertEquals(['email'], array_keys((array) $cm->getColumns()));
    }

    public function testExclusionRetrieval()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('user')->columns(['user_id', 'email', 'displayname', 'username', 'password']);

        $source = new SqlSource($this->adapter, $select);

        $excluded = ['user_id', 'email'];
        $cm = $source->getColumnModel();
        $cm->exclude($excluded);
        self::assertEquals($excluded, $cm->getExcluded());

        $data = $source->getData();
        $this->isInstanceOf('Soluble\FlexStore\ResultSet\ResultSet');

        $d = $data->toArray();
        $first = array_keys($d[0]);

        self::assertCount(3, $first);
        self::assertEquals('displayname', array_shift($first));
        self::assertEquals('username', array_shift($first));
    }
}
