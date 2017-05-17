<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 20016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace Soluble\FlexStore\Source\DbWrapper;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Options;
use ArrayObject;
use Soluble\DbWrapper\Adapter\AdapterInterface;
use Soluble\DbWrapper\Result\Resultset as DbWrapperResultset;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\Type\MetadataMapper;
use Soluble\Metadata\Reader as MetadataReader;

class QuerySource extends AbstractSource implements QueryableSourceInterface
{
    /**
     * @var string
     */
    protected $query;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Initial params received in the constructor.
     *
     * @var ArrayObject
     */
    protected $params;

    /**
     * The query string contains the query as it has been crafted (with options, limits...).
     *
     * @var string
     */
    protected $query_string;

    /**
     * @var ColumnModel
     */
    protected $columnModel;

    /**
     * @param AdapterInterface $adapter
     * @param string           $query
     */
    public function __construct(AdapterInterface $adapter, $query = null)
    {
        $this->adapter = $adapter;

        if ($query !== null) {
            $this->setQuery($query);
        }
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string  $query
     * @param Options $options
     *
     * @return string query
     */
    protected function assignOptions($query, Options $options)
    {
        if ($options->hasLimit()) {
            /*
             * For mysql queries, to allow counting rows we must prepend
             * SQL_CALC_FOUND_ROWS to the select quantifiers
             */
            if ($options->getLimit() > 0) {
                $calc_found_rows = 'SQL_CALC_FOUND_ROWS';
                if (!preg_match("/$calc_found_rows/", $query)) {
                    $q = trim($query);
                    $query = preg_replace('/^select\b/i', "SELECT $calc_found_rows", $q);
                }
            }

            // mysql only, @todo make it rule everything (use traits)
            $replace_regexp = "LIMIT[\s]+[\d]+((\s*,\s*\d+)|(\s+OFFSET\s+\d+)){0,1}";

            $search_regexp = "$replace_regexp";
            if (!preg_match("/$search_regexp$/i", $query)) {
                // Limit is not already present
                $query .= ' LIMIT ' . $options->getLimit();
            } else {
                $query = preg_replace("/($replace_regexp)/i", 'LIMIT ' . $options->getLimit(), $query);
            }

            if ($options->hasOffset()) {
                $query .= ' OFFSET ' . $options->getOffset();
            }
        }

        return $query;
    }

    /**
     * @param Options $options
     *
     * @throws Exception\EmptyQueryException
     * @throws Exception\ErrorException
     *
     * @return ResultSet
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            $options = $this->getOptions();
        }

        $this->query_string = $this->assignOptions($this->query, $options);

        try {
            $results = $this->adapter->query($this->query_string, DbWrapperResultset::TYPE_ARRAYOBJECT);

            $r = new ResultSet($results);
            $r->setSource($this);
            $r->setHydrationOptions($options->getHydrationOptions());

            if ($options->hasLimit() && $options->getLimit() > 0) {
                //$row = $this->adapter->query('select FOUND_ROWS() as total_count')->execute()->current();
                $row = $this->adapter->query('select FOUND_ROWS() as total_count')->current();
                $r->setTotalRows($row['total_count']);
            } else {
                $r->setTotalRows($r->count());
            }
        } catch (\Exception $e) {
            throw new Exception\ErrorException(__METHOD__ . ': Cannot retrieve data (' . $e->getMessage() . ')');
        }

        return $r;
    }

    public function loadDefaultColumnModel()
    {
        $metadata_columns = $this->getMetadataReader()->getColumnsMetadata($this->query);
        $this->setColumnModel(MetadataMapper::getColumnModelFromMetadata($metadata_columns));
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\UnsupportedFeatureException
     */
    public function getMetadataReader()
    {
        if ($this->metadataReader === null) {
            $this->setMetadataReader($this->getDefaultMetadataReader());
        }

        return $this->metadataReader;
    }

    /**
     * @throws Exception\UnsupportedFeatureException
     */
    protected function getDefaultMetadataReader()
    {
        $conn = $this->adapter->getConnection()->getResource();
        $class = strtolower(get_class($conn));
        switch ($class) {
            case 'pdo':
                return new MetadataReader\PdoMysqlMetadataReader($conn);
            case 'mysqli':
                return new MetadataReader\MysqliMetadataReader($conn);
            default:
                throw new Exception\UnsupportedFeatureException(__METHOD__ . " Does not support default metadata reader for driver '$class'");
        }
    }

    /**
     * Return the query string that was executed with options etc.
     *
     * @throws Exception\InvalidUsageException
     *
     * @return string
     */
    public function getQueryString()
    {
        if ($this->query_string == '') {
            throw new Exception\InvalidUsageException(__METHOD__ . ': Invalid usage, getQueryString must be called after data has been loaded (performance reason).');
        }

        return str_replace("\n", ' ', $this->query_string);
    }

    /**
     * Return the query string.
     *
     * @throws Exception\InvalidUsageException
     *
     * @return string
     */
    public function __toString()
    {
        if (trim($this->query) == '') {
            throw new Exception\InvalidUsageException(__METHOD__ . ': Empty query given.');
        }

        return $this->query;
    }
}
