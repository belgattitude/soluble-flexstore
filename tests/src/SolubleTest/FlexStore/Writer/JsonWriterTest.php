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

namespace SolubleTest\FlexStore\Writer;

use PHPUnit\Framework\TestCase;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Soluble\FlexStore\FlexStore;
use Soluble\FlexStore\Formatter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use DateTime;
use Soluble\FlexStore\Writer\JsonWriter;

class JsonWriterTest extends TestCase
{
    /**
     * @var JsonWriter
     */
    protected $jsonWriter;

    /**
     * @var SqlSource
     */
    protected $source;

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new \Zend\Db\Sql\Select();
        $select->from('product_brand')->where("reference = 'STAG'");

        $this->source = new SqlSource($this->adapter, $select);

        $this->jsonWriter = new JsonWriter();
        $this->jsonWriter->setStore(new FlexStore($this->source));
    }

    public function testGetData()
    {
        $data = $this->jsonWriter->getData();
        self::assertJson($data);
        $d = json_decode($data, $assoc = true);
        self::assertArrayHasKey('total', $d);
        self::assertArrayHasKey('start', $d);
        self::assertArrayHasKey('limit', $d);
        self::assertArrayHasKey('request_id', $d);
        self::assertArrayHasKey('data', $d);
        self::assertTrue($d['success']);
        self::assertArrayHasKey('timestamp', $d);
        self::assertNull($d['request_id']);
        $timestamp = DateTime::createFromFormat(DateTime::W3C, $d['timestamp']);

        self::assertEquals($timestamp->format(DateTime::W3C), $d['timestamp']);
        self::assertEquals($d['total'], count($d['data']));
        self::assertArrayNotHasKey('query', $d);
    }

    public function testGetDataWithGlobalLimit()
    {
        $select = new \Zend\Db\Sql\Select();
        $select->from('product');

        $source = new SqlSource($this->adapter, $select);
        $store = new FlexStore($source);

        $limit = 2;

        $store->getOptions()->setLimit($limit);
        $jsonWriter = new JsonWriter($store);

        $data = json_decode($jsonWriter->getData(), true);
        self::assertEquals($limit, $data['limit']);
        self::assertGreaterThan($limit, $data['total']);
        self::assertEquals($limit, count($data['data']));
    }

    public function testGetDataWithRequestId()
    {
        $this->jsonWriter->setRequestId(12321321321);
        $data = $this->jsonWriter->getData();
        self::assertJson($data);
        $d = json_decode($data, $assoc = true);
        self::assertEquals(12321321321, $d['request_id']);
    }

    public function testGetDataWithDebug()
    {
        $this->jsonWriter->setDebug($debug = true);
        $data = $this->jsonWriter->getData();
        self::assertJson($data);
        $d = json_decode($data, $assoc = true);

        self::assertArrayHasKey('query', $d);
    }

    public function testColumnModel()
    {
        $store = new FlexStore($this->getTestSource());
        $cm = $store->getColumnModel();

        $locale = 'en_US';
        $formatter = Formatter::create('currency', [
            'currency_code' => new \Soluble\FlexStore\Formatter\RowColumn('currency_reference'),
            'locale' => $locale
        ]);

        $cm->search()->regexp('/price/')->setFormatter($formatter);

        $formatted_data = $store->getData()->toArray();
        self::assertEquals('CN¥15.30', $formatted_data[0]['list_price']);

        $writer = new JsonWriter($store);
        $json_data = json_decode($writer->getData(), $assoc = true);

        self::assertNotEquals('CN¥15.30', $json_data['data'][0]['list_price']);
        self::assertEquals(15.3, (float) $json_data['data'][0]['list_price']);
    }

    /**
     * @return SqlSource
     */
    protected function getTestSource()
    {
        $source = new SqlSource($this->adapter);
        $select = $source->select();
        $select->from(['p' => 'product'])
                ->join(['ppl' => 'product_pricelist'], 'ppl.product_id = p.product_id', [], Select::JOIN_LEFT)
                ->join(['p18' => 'product_translation'], new Expression("p.product_id = p18.product_id and p18.lang = 'fr'"), [], Select::JOIN_LEFT)
                ->limit(100);

        $select->columns([
            'test_chars' => new Expression('"french accents éàùêûçâµè and chinese 请收藏我们的网址"'),
            'product_id' => new Expression('p.product_id'),
            'brand_id' => new Expression('p.brand_id'),
            'reference' => new Expression('p.reference'),
            'description' => new Expression('p.description'),
            'volume' => new Expression('p.volume'),
            'weight' => new Expression('p.weight'),
            'barcode_ean13' => new Expression('1234567890123'),
            'created_at' => new Expression('NOW()'),
            'price' => new Expression('ppl.price'),
            'discount_1' => new Expression('ppl.discount_1'),
            'promo_start_at' => new Expression('ppl.promo_start_at'),
            'promo_end_at' => new Expression('cast(NOW() as date)'),
            'title_fr' => new Expression('p18.title'),
            'list_price' => new Expression('ppl.list_price'),
            'public_price' => new Expression('ppl.public_price'),
            'currency_reference' => new Expression("'CNY'")
        ]);

        return $source;
    }

    public function testGetHTTPHeaders()
    {
        $headers = $this->jsonWriter->getHttpHeaders();
        self::assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        self::assertEquals('application/json', $headers->getContentType());
        self::assertEquals('UTF-8', strtoupper($headers->getCharset()));
    }
}
