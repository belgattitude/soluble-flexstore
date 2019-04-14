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

namespace SolubleTest\FlexStore\Source\DbWrapper;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Options;
use Soluble\FlexStore\Source\DbWrapper\QuerySource;
use Soluble\DbWrapper\Adapter\AdapterInterface;
use Soluble\DbWrapper\AdapterFactory;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-14 at 12:05:43.
 */
class QuerySourceTest extends TestCase
{
    /**
     * @var QuerySource
     */
    protected $source;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $zendAdapter = \SolubleTestFactories::getDbAdapter();

        $this->adapter = AdapterFactory::createAdapterFromZendDb2($zendAdapter);

        $query = 'select * from user';

        $this->source = new QuerySource($this->adapter, $query);
    }

    public function testGetData()
    {
        $source1 = $this->getNewSource();
        $source2 = $this->getNewSource();

        $data = $source1->getData();
        $this->isInstanceOf('Soluble\FlexStore\ResultSet\ResultSet');
        $d = $data->toArray();
        self::assertInternalType('array', $d);
        self::assertArrayHasKey('user_id', $d[0]);
        self::assertArrayHasKey('email', $d[0]);

        $options = new Options();
        $options->setLimit(10, 0);

        $data2 = $source2->getData($options);
        $d2 = $data2->toArray();

        self::assertInternalType('array', $d2);

        self::assertArrayHasKey('user_id', $d2[0]);
        self::assertArrayHasKey('email', $d2[0]);
        self::assertEquals($d[0], $d2[0]);
    }

    public function testGetMetadata()
    {
        $metadata = $this->source->getMetadataReader();
        self::assertInstanceOf('\Soluble\Metadata\Reader\AbstractMetadataReader', $metadata);
    }

    public function testGetColumnModel()
    {
        $columnModel = $this->source->getColumnModel();
        self::assertInstanceOf('\Soluble\FlexStore\Column\ColumnModel', $columnModel);
        $columns = $columnModel->getColumns();
        self::assertInstanceOf('ArrayObject', $columns);
        foreach ($columns as $column) {
            self::assertFalse($column->isVirtual());
        }
    }

    public function testGetMetadatareader()
    {
        $source = $this->getNewSource();
        $mr = $source->getMetadataReader();
        self::assertInstanceOf('Soluble\Metadata\Reader\AbstractMetadataReader', $mr);
    }

    public function testCalcFoundRowsAndWithZeroLimit()
    {
        $source = new QuerySource($this->adapter, 'select * from product');

        $options = new Options();
        $options->setLimit(0, 0);

        $data = $source->getData($options);
        self::assertEquals(0, $data->count());
        self::assertEquals(0, $data->getTotalRows());

        // Edge, test if SQL_CALC_FOUND_ROWS was really injected
        $query = $source->getQueryString();
        self::assertNotContains('SQL_CALC_FOUND_ROWS', $query);
        self::assertContains('LIMIT 0 OFFSET 0', $query);
    }

    public function testCalcFoundRowsAndOptions()
    {
        $source = new QuerySource($this->adapter, 'SELECT * from product');

        $options = new Options();
        $options->setLimit(2, 0);

        $data = $source->getData($options);

        self::assertEquals(2, $data->count());
        self::assertGreaterThan(2, $data->getTotalRows());

        // Edge, test if SQL_CALC_FOUND_ROWS was really injected
        $query = $source->getQueryString();
        self::assertContains('SQL_CALC_FOUND_ROWS', $query);
        self::assertContains('LIMIT 2 OFFSET 0', $query);
    }

    /**
     * @return QuerySource
     */
    protected function getNewSource()
    {
        $source = new QuerySource($this->adapter, 'select * from user');

        return $source;
    }
}
