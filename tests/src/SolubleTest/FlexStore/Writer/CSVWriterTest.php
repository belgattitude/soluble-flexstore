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
use Soluble\FlexStore\Store\StoreInterface;
use Soluble\FlexStore\Writer\CSVWriter;
use Soluble\FlexStore\Writer\Exception;
use Soluble\FlexStore\Source\Zend\SqlSource;
use Soluble\FlexStore\FlexStore;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\Source\DbWrapper\QuerySource;
use Soluble\DbWrapper\AdapterFactory;

class CSVWriterTest extends TestCase
{
    /**
     * @var CSVWriter
     */
    protected $csvWriter;

    /**
     * @var SqlSource
     */
    protected $source;

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->adapter = \SolubleTestFactories::getDbAdapter();
        $select = new Select();
        $select->from('product_category_translation')->where("lang = 'fr'")->limit(50);

        $this->source = $this->getSource($select);
        $this->store = $this->getStore($this->source);

        $this->csvWriter = new CSVWriter($this->store);
    }

    /**
     * @param Select $select
     *
     * @return SqlSource
     */
    protected function getSource(Select $select = null)
    {
        return new SqlSource($this->adapter, $select);
    }

    /**
     * @param SqlSource $sqlSource
     *
     * @return FlexStore
     */
    protected function getStore(SqlSource $sqlSource = null)
    {
        return new FlexStore($sqlSource);
    }

    public function testGetEmptyData()
    {
        $enclosure = '"';
        $this->csvWriter->setOptions(
            [
                    'field_separator' => CSVWriter::SEPARATOR_TAB,
                    'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
                    'enclosure' => $enclosure,
                    'charset' => 'UTF-8'
                    ]
        );

        $options = new \Soluble\FlexStore\Options();
        $options->setLimit(0);
        $data = $this->csvWriter->getData($options);
        self::assertInternalType('string', $data);

        $data = explode(CSVWriter::SEPARATOR_NEWLINE_UNIX, $data);
        $header = str_getcsv($data[0], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
        $columns = array_keys((array) $this->source->getColumnModel()->getColumns());
        self::assertEquals($columns, $header);
    }

    public function testGetData()
    {
        $data = $this->csvWriter->getData();
        self::assertInternalType('string', $data);
    }

    public function testCharsetTranslit()
    {
        $adapter = AdapterFactory::createAdapterFromZendDb2($this->adapter);
        $enclosure = '"';
        $cases = [
            [
                'query' => 'select "Blue Saitensatz für Klassikgitarre" as col_title',
                'ignore_translit_error' => false,
                'should_pass' => true,
                'expected' => 'Blue Saitensatz für Klassikgitarre',
                'charset' => 'ISO-8859-1',
            ],
            [
                'query' => 'select "Saitens„tze fr Konzertgitarren" as col_title',
                'ignore_translit_error' => true,
                'should_pass' => true,
                'expected' => 'Saitens,,tze fr Konzertgitarren',
                'charset' => 'ISO-8859-1',
            ],
            [
                'query' => 'select "Saitens„tze fr Konzertgitarren" as col_title',
                'ignore_translit_error' => false,
                'should_pass' => false,
                'expected' => 'Should fail !!!',
                'charset' => 'ISO-8859-1',
            ]
        ];

        foreach ($cases as $idx => $case) {
            $query = $case['query'];
            $source = new QuerySource($adapter, $query);
            $store = new FlexStore($source);

            try {
                $csvWriter = new CSVWriter($store);
                $csvWriter->setOptions([
                        'charset' => $case['charset'],
                        'ignore_translit_error' => $case['ignore_translit_error'],
                        'enclosure' => $enclosure
                ]);

                $data = $csvWriter->getData();
                $exploded = explode(CSVWriter::SEPARATOR_NEWLINE_UNIX, $data);

                $line1 = str_getcsv($exploded[1], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
                $cell = $line1[0];
                $utf8_cell = utf8_encode($cell);

                self::assertEquals($case['expected'], $utf8_cell, "Translit error with query number $idx");
            } catch (Exception\CharsetConversionException $e) {
                if ($case['should_pass']) {
                    throw $e;
                } else {
                    // nothing
                }
            }
        }
    }

    public function testGetDataLatin1Charset()
    {
        $enclosure = '"';
        $this->csvWriter->setOptions(
            [
                    'field_separator' => CSVWriter::SEPARATOR_TAB,
                    'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
                    'enclosure' => $enclosure,
                    'charset' => 'ISO-8859-1'
                    ]
        );

        $data = $this->csvWriter->getData();
        self::assertInternalType('string', $data);
        $data = explode(CSVWriter::SEPARATOR_NEWLINE_UNIX, $data);
        $line0 = str_getcsv($data[0], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
        self::assertInternalType('array', $line0);
        self::assertEquals($line0[1], 'category_id');

        $select = new \Zend\Db\Sql\Select();
        $select->from('product_category_translation')->where("lang = 'fr' and category_id = 988")->limit(50);

        $this->csvWriter->setStore(new FlexStore($this->getSource($select)));
        $data = $this->csvWriter->getData();
        $data = explode(CSVWriter::SEPARATOR_NEWLINE_UNIX, $data);
        $line1 = str_getcsv($data[1], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
        self::assertInternalType('array', $line1);
        $title = $line1[4];

        $header = str_getcsv($data[0], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
        $columns = array_keys((array) $this->source->getColumnModel()->getColumns());
        self::assertEquals($columns, $header);

        self::assertTrue(mb_check_encoding($title, 'ISO-8859-1'));
        self::assertFalse(mb_check_encoding($title, 'UTF-8'));
        self::assertFalse(mb_check_encoding($title, 'ASCII'));
        self::assertEquals(utf8_decode('Modèles Electriques'), $title);

        $headers = $this->csvWriter->getHttpHeaders();
        self::assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        self::assertEquals('text/csv', $headers->getContentType());
        self::assertEquals('ISO-8859-1', strtoupper($headers->getCharset()));
    }

    public function testGetDataLatin1CharsetThrowsCharsetException()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>')) {
            $this->expectException('Soluble\FlexStore\Writer\Exception\CharsetConversionException');

            $source = new SqlSource($this->adapter);
            $select = $source->select();

            $select->from('user')->columns(
                [
                'user_id' => new Expression('user_id'),
                'test' => new Expression('"french accents éàùêûçâµè and chinese 请收藏我们的网址"')]
            );
            $store = new FlexStore($source);

            $writer = new CSVWriter($store);
            $writer->setOptions(
                [
                        'charset' => 'ISO-8859-1'
                        ]
            );
            $data = $writer->getData();
        } else {
            $this->markTestSkipped('Only valid for PHP 5.4+ version');
        }
    }

    public function testGetDataUTF8Charset()
    {
        $enclosure = '"';
        $this->csvWriter->setOptions(
            [
                    'field_separator' => CSVWriter::SEPARATOR_TAB,
                    'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
                    'enclosure' => $enclosure,
                    //'charset' => 'ISO-8859-1'
                    ]
        );

        $data = $this->csvWriter->getData();
        self::assertInternalType('string', $data);
        $data = explode(CSVWriter::SEPARATOR_NEWLINE_UNIX, $data);
        $line0 = str_getcsv($data[0], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
        self::assertInternalType('array', $line0);
        self::assertEquals($line0[1], 'category_id');

        $select = new \Zend\Db\Sql\Select();
        $select->from('product_category_translation')->where("lang = 'fr' and category_id = 988")->limit(50);

        $this->csvWriter->setStore(new FlexStore($this->getSource($select)));
        $data = $this->csvWriter->getData();

        $data = explode(CSVWriter::SEPARATOR_NEWLINE_UNIX, $data);
        $line1 = str_getcsv($data[1], CSVWriter::SEPARATOR_TAB, $enclosure, $escape = null);
        self::assertInternalType('array', $line1);
        $title = $line1[4];

        self::assertTrue(mb_check_encoding($title, 'UTF-8'));
        self::assertFalse(mb_check_encoding($title, 'ASCII'));
        self::assertNotEquals(utf8_decode($title), $title);
        self::assertEquals('Modèles Electriques', $title);

        $headers = $this->csvWriter->getHttpHeaders();
        self::assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        self::assertEquals('text/csv', $headers->getContentType());
        self::assertEquals('UTF-8', strtoupper($headers->getCharset()));
    }

    public function testGetDataWithOptionsThrowsInvalidArgumentException()
    {
        $this->expectException('Soluble\FlexStore\Exception\InvalidArgumentException');
        $this->csvWriter->setOptions(
            [
                    'rossssss' => 'line',
                    ]
        );

        $data = $this->csvWriter->getData();
    }

    public function testGetDataEscapeDelimiter()
    {
        $enclosure = '"';
        $this->csvWriter->setOptions(
            [
                    'field_separator' => CSVWriter::SEPARATOR_SEMICOLON,
                    'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
                    'enclosure' => $enclosure,
                    'charset' => 'ISO-8859-1',
                    'escape' => '\\'
                    ]
        );

        $select = new \Zend\Db\Sql\Select();
        $select->from(['pc18' => 'product_category_translation'])
               ->columns([
                   'category_id',
                   'test' => new \Zend\Db\Sql\Expression("'alpha; beta;'")
               ])
               ->where("lang = 'fr' and category_id = 988");

        $this->csvWriter->setStore(new FlexStore($this->getSource($select)));
        $data = $this->csvWriter->getData();
        self::assertContains('alpha\; beta\;', $data);
    }

    public function testGetDataEnclosureDelimiterWithoutEscape()
    {
        $enclosure = '"';
        $this->csvWriter->setOptions(
            [
                    'field_separator' => CSVWriter::SEPARATOR_SEMICOLON,
                    'line_separator' => CSVWriter::SEPARATOR_NEWLINE_UNIX,
                    'enclosure' => $enclosure,
                    'charset' => 'ISO-8859-1',
                    'escape' => ''
                    ]
        );

        $select = new \Zend\Db\Sql\Select();
        $select->from(['pc18' => 'product_category_translation'])
               ->columns([
                   'category_id',
                   'test' => new \Zend\Db\Sql\Expression("'alpha; beta;'")
               ])
               ->where("lang = 'fr' and category_id = 988");

        $this->csvWriter->setStore(new FlexStore($this->getSource($select)));
        $data = $this->csvWriter->getData();

        self::assertContains('"alpha; beta;"', $data);
    }

    public function testGetHTTPHeaders()
    {
        $headers = $this->csvWriter->getHttpHeaders();
        self::assertInstanceOf("Soluble\FlexStore\Writer\Http\SimpleHeaders", $headers);
        self::assertEquals('text/csv', $headers->getContentType());
        self::assertEquals('UTF-8', strtoupper($headers->getCharset()));
    }
}
