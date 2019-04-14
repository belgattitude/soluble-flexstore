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
use DateTime;
use Soluble\FlexStore\Writer\SimpleXmlWriter;

class SimpleXmlWriterTest extends TestCase
{
    /**
     * @var SimpleXmlWriter
     */
    protected $xmlWriter;

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
        $select->from('product_brand');

        $this->source = new SqlSource($this->adapter, $select);

        $this->xmlWriter = new SimpleXmlWriter();
        $this->xmlWriter->setStore(new FlexStore($this->source));
    }

    public function testConstructor()
    {
        $xmlWriter = new SimpleXmlWriter(new FlexStore($this->source));
        self::assertInstanceOf('\Soluble\FlexStore\Writer\AbstractWriter', $xmlWriter);
    }

    /**
     * @covers \Soluble\FlexStore\Writer\SimpleXmlWriter::getData
     */
    public function testGetData()
    {
        $this->xmlWriter->setRowTag('row');

        $data = $this->xmlWriter->getData();
        self::assertInternalType('string', $data);
        $xml = new \SimpleXMLElement($data);

        self::assertInternalType('numeric', (string) $xml->total);
        self::assertInternalType('numeric', (string) $xml->success);

        $timestamp = DateTime::createFromFormat(DateTime::W3C, (string) $xml->timestamp);

        self::assertEquals($timestamp->format(DateTime::W3C), (string) $xml->timestamp);

        self::assertNotEmpty($xml->data->row[0]->reference);
    }

    public function testGetDataWithOptions()
    {
        $this->xmlWriter->setOptions(
            [
                    'row_tag' => 'line',
                    'body_tag' => 'result'
                    ]
        );

        $data = $this->xmlWriter->getData();
        self::assertInternalType('string', $data);

        $xml = new \SimpleXMLElement($data);

        self::assertInternalType('numeric', (string) $xml->total);
        self::assertInternalType('numeric', (string) $xml->success);
        self::assertNotEmpty($xml->data->line[0]->reference);
    }

    /**
     * @covers \Soluble\FlexStore\Writer\SimpleXmlWriter::getOptions
     */
    public function testGetDataWithOptionsThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Exception\InvalidArgumentException');
        $this->xmlWriter->setOptions(
            [
                    'rossssss' => 'line',
                    'body_tag' => 'result'
                    ]
        );

        $data = $this->xmlWriter->getData();
    }

    public function testGetHTTPHeaders()
    {
        $headers = $this->xmlWriter->getHttpHeaders();
        self::assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        self::assertEquals('application/xml', $headers->getContentType());
        self::assertEquals('UTF-8', $headers->getCharset());
        self::assertEquals('attachement', $headers->getContentDispositionType());
    }
}
