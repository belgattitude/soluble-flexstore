<?php

/**
 *
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Source\DbWrapper;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Options;
use ArrayObject;
use Soluble\DbWrapper\Adapter\AdapterInterface;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\Type\MetadataMapper;
use Soluble\Metadata\Reader as MetadataReader;

class QuerySource extends AbstractSource implements QueryableSourceInterface
{

    /**
     *
     * @var string
     */
    protected $query;

    /**
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Initial params received in the constructor
     * @var ArrayObject
     */
    protected $params;

    /**
     *
     * @var string
     */
    protected $query_string;



    /**
     *
     * @var ColumnModel
     */
    protected $columnModel;

    /**
     *
     * @param AdapterInterface $adapter
     * @param string $query
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
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }


    /**
     *
     * @param Select $select
     * @param Options $options
     * @return Select
     */
    protected function assignOptions(Select $select, Options $options)
    {
        if ($options->hasLimit()) {
            $select->limit($options->getLimit());
            if ($options->hasOffset()) {
                $select->offset($options->getOffset());
            }
            /**
             * For mysql queries, to allow counting rows we must prepend
             * SQL_CALC_FOUND_ROWS to the select quantifiers
             */
            $calc_found_rows = 'SQL_CALC_FOUND_ROWS';
            $quant_state = $select->getRawState($select::QUANTIFIER);
            if ($quant_state !== null) {
                if ($quant_state instanceof Expression) {
                    $quant_state->setExpression($calc_found_rows . ' ' . $quant_state->getExpression());
                } elseif (is_string($quant_state)) {
                    $quant_state = $calc_found_rows . ' ' . $quant_state;
                }
                $select->quantifier($quant_state);
            } else {
                $select->quantifier(new Expression($calc_found_rows));
            }
        }
        return $select;
    }

    /**
     *
     * @param Options $options
     * @throws Exception\EmptyQueryException
     * @throws Exception\ErrorException
     * @return ResultSet
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            $options = $this->getOptions();
        }

        // todo
        //$query = $this->assignOptions(clone $this->query, $options);


        $sql_string = $this->query;

        try {
            $results = $this->adapter->query($sql_string);



            $r = new ResultSet($results);
            $r->setSource($this);
            $r->setHydrationOptions($options->getHydrationOptions());

            if ($options->hasLimit()) {
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

    /**
     *
     */
    public function loadDefaultColumnModel()
    {
        $metadata_columns = $this->getMetadataReader()->getColumnsMetadata($this->query);
        $this->setColumnModel(MetadataMapper::getColumnModelFromMetadata($metadata_columns));
    }

    /**
     * {@inheritdoc}
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
     * Return the query string that was executed
     * @throws Exception\InvalidUsageException
     * @return string
     */
    public function getQueryString()
    {
        if ($this->query_string == '') {
            throw new Exception\InvalidUsageException(__METHOD__ . ": Invalid usage, getQueryString must be called after data has been loaded (performance reason).");
        }
        return str_replace("\n", ' ', $this->query_string);
    }

    /**
     * Return the query string
     *
     * @throws Exception\InvalidUsageException
     * @return string
     */
    public function __toString()
    {
        if ($this->query_string != '') {
            $sql = str_replace("\n", ' ', $this->query_string);
        } elseif ($this->select !== null) {
            $sql = $this->sql->getSqlStringForSqlObject($this->select);
        } else {
            throw new Exception\InvalidUsageException(__METHOD__ . ": No select given.");
        }
        return $sql;
    }
}
