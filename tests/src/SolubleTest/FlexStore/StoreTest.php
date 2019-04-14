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

namespace SolubleTest\FlexStore;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\FlexStore;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-10-01 at 15:15:02.
 */
class StoreTest extends TestCase
{
    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var SqlSource
     */
    protected $source;

    /**
     * Dummy select.
     *
     * @var Select
     */
    protected $select;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $this->source = new SqlSource($this->adapter);
        $this->select = new Select();
        $this->select->from('user');
    }

    public function testBehaviour()
    {
        $source = new SqlSource($this->adapter);
        $source->select()
               ->from(['ttt' => 'test_table_types']);

        $store = new FlexStore($source);
        $cm = $store->getColumnModel();
        //$config = new Zend\Config\Config();
        //$cm->mergeConfiguration($config);
        $cm->exclude(['test_multipoint']);

        $search = $cm->search();
        $search->regexp('/multi/')->setExcluded(true);
        $search->regexp('/^test\_/')->setExcluded(true);
        $search->in(['test_char_10'])->setExcluded(false);

        $data = $store->getData()->toArray();
        $keys = implode(',', array_keys($data[0]));
        self::assertEquals('id,test_char_10', $keys);

        $search->all()->setExcluded(true);
        $search->regexp('/\_10$/')->setExcluded($excluded = false);

        $data = $store->getData()->toArray();
        $keys = implode(',', array_keys($data[0]));
        self::assertEquals('test_char_10,test_varbinary_10', $keys);
    }

    public function testGetOptions()
    {
        $this->source->select()->from('product');
        $store = new FlexStore($this->source);
        $options = $store->getOptions();
        self::assertInstanceOf('Soluble\FlexStore\Options', $options);
        $options->setLimit(2);
        $data = $store->getData()->toArray();
        self::assertCount(2, $data);
    }

    public function testGetSource()
    {
        $this->source->select()->from('user');
        $store = new FlexStore($this->source);
        $source = $store->getSource();
        self::assertInstanceOf('Soluble\FlexStore\Source\Zend\SqlSource', $source);
    }

    public function testGetData()
    {
        $source = $this->source;
        $source->setSelect($this->select);
        $store = new FlexStore($source);
        $resultset = $store->getData();
        self::assertInstanceOf('Soluble\FlexStore\ResultSet\ResultSet', $resultset);
    }

    public function testGetDataThrowsEmptyQueryException()
    {
        $this->expectException('Soluble\FlexStore\Exception\EmptyQueryException');
        $store = new FlexStore($this->source);
        $resultset = $store->getData();
        self::assertInstanceOf('Soluble\FlexStore\ResultSet\ResultSet', $resultset);
    }

    public function testGetColumnModel()
    {
        $this->source->select()->from('user');
        $store = new FlexStore($this->source);
        $cm = $store->getColumnModel();
        self::assertInstanceOf('Soluble\FlexStore\Column\ColumnModel', $cm);
    }
}
