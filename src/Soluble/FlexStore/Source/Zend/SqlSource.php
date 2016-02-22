<?php

/**
 *
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Source\Zend;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\ResultSet\ResultSet;
use Soluble\FlexStore\Exception;
use Soluble\FlexStore\Options;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use ArrayObject;
use Soluble\FlexStore\Column\ColumnModel;
use Soluble\FlexStore\Column\Type\MetadataMapper;
use Soluble\Metadata\Reader\AbstractMetadataReader;
use Soluble\Metadata\Reader as MetadataReader;

class SqlSource extends AbstractSource implements QueryableSourceInterface
{
    /**
     * @var Sql
     */
    protected $sql;

    /**
     *
     * @var Select
     */
    protected $select;

    /**
     *
     * @var Adapter
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
     * @var \Zend\Db\Adapter\Driver\Mysqli\Statement
     */
    protected static $cache_stmt_prototype;

    /**
     *
     * @var \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected static $cache_result_prototype;

    /**
     *
     * @var ColumnModel
     */
    protected $columnModel;

    /**
     *
     * @param Adapter $adapter
     * @param Select $select
     */
    public function __construct(Adapter $adapter, Select $select = null)
    {
        $this->adapter = $adapter;
        $this->sql = new Sql($this->adapter);
        if ($select !== null) {
            $this->setSelect($select);
        }
    }

    /**
     * @param Select
     * @return SqlSource
     */
    public function setSelect(Select $select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     *
     * @return Select
     */
    public function getSelect()
    {
        return $this->select();
    }

    /**
     *
     * @return Select
     */
    public function select()
    {
        if ($this->select === null) {
            $this->select = $this->sql->select();
        }
        return $this->select;
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

        $select = $this->assignOptions(clone $this->getSelect(), $options);


        $sql = new Sql($this->adapter);
        $sql_string = (string) $sql->getSqlStringForSqlObject($select);
        //echo $this->select->getSqlString($this->adapter->getPlatform());
        //echo "----" . var_dump($sql_string) . "----\n";
        // In ZF 2.3.0 an empty query will return SELECT .*
        // In ZF 2.4.0 and empty query will return SELECT *
        if (in_array($sql_string, ['', 'SELECT .*', 'SELECT *'])) {
            throw new Exception\EmptyQueryException(__METHOD__ . ': Cannot return data of an empty query');
        }
        $this->query_string = $sql_string;

        try {
            $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
            //$stmt = $sql->prepareStatementForSqlObject( $select );
            //$results = $stmt->execute();
            //var_dump(get_class($results));

            $r = new ResultSet($results);
            $r->setSource($this);
            $r->setHydrationOptions($options->getHydrationOptions());

            if ($options->hasLimit()) {
                //$row = $this->adapter->query('select FOUND_ROWS() as total_count')->execute()->current();
                $row = $this->adapter->createStatement('select FOUND_ROWS() as total_count')->execute()->current();
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
        $sql = new Sql($this->adapter);
        $select = clone $this->select;
        $select->limit(0);
        $sql_string = $sql->getSqlStringForSqlObject($select);
        $metadata_columns = $this->getMetadataReader()->getColumnsMetadata($sql_string);
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
        $conn = $this->adapter->getDriver()->getConnection()->getResource();
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
