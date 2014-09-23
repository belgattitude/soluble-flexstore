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
use Soluble\FlexStore\Column\Column;
use Soluble\Flexstore\Metadata\Reader\AbstractMetadataReader;
use Soluble\FlexStore\Metadata\Reader as MetadataReader;



class SelectSource extends AbstractSource implements QueryableSourceInterface
{

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
     * @var Zend\Db\Adapter\Driver\ResultInterface
     */
    protected static $cache_result_prototype;


    /**
     *
     * @var ColumnModel
     */
    protected $columnModel;

    /**
     *
     * @param array|ArrayObject $params
     * @throws Exception\InvalidArgumentException
     * @throws Exception\MissingArgumentException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $params = new ArrayObject($params);
        } elseif (!$params instanceof ArrayObject) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ": Params must be either an ArrayObject or an array");
        }

        if ($params->offsetExists('select')) {
            if ($params['select'] instanceof Select) {
                $this->select = $params['select'];
            } else {
                throw new Exception\InvalidArgumentException(__METHOD__ . ": Param 'source' must be an instance of Zend\Db\Sql\Select");
            }
        } else {
            throw new Exception\MissingArgumentException(__METHOD__ . ": Missing param 'select'");
        }

        if ($params->offsetExists('adapter')) {
            if ($params['adapter'] instanceof \Zend\Db\Adapter\Adapter) {
                $this->adapter = $params['adapter'];
            } else {
                throw new Exception\InvalidArgumentException(__METHOD__ . ": Param 'adapter' must be an instance of Zend\Db\Adapter\Adapter");
            }
        } else {
            throw new Exception\MissingArgumentException(__METHOD__ . ": Missing param 'adapter'");
        }
        $this->params = $params;
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
            if (is_numeric($options->getOffset())) {
                $select->offset($options->getOffset());
            }
            $select->quantifier(new Expression('SQL_CALC_FOUND_ROWS'));
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

        $select = $this->assignOptions(clone $this->select, $options);

        $sql = new Sql($this->adapter);
        $sql_string = (string) $sql->getSqlStringForSqlObject($select);

        // In  ZF 2.3.0 an empty query will return SELECT .*
        if (in_array($sql_string, array('', 'SELECT .*'))) {
            throw new Exception\EmptyQueryException(__METHOD__ . ': Cannot return data of an empty query');
        }
        $this->query_string = $sql_string;


        // In case of unbuffered results (default on mysqli) !!!
        // Seems to not be needed anymore in ZF 2.3+
        // Uncomment if necessary, see also below is_mysqli
        /*
        $is_mysqli = false;
        $driver = $this->adapter->getDriver();
        if (false && $driver instanceof \Zend\Db\Adapter\Driver\Mysqli\Mysqli) {
            $stmt_prototype_backup = $driver->getStatementPrototype();
            if (self::$cache_stmt_prototype === null) {
                // With buffer results
                self::$cache_stmt_prototype = new \Zend\Db\Adapter\Driver\Mysqli\Statement($buffer=true);
            }
            $driver->registerStatementPrototype(self::$cache_stmt_prototype);
            $is_mysqli = true;
        }
        */
       
        
        /**
         * Check whether there's a column model
         */
        $limit_columns = false;
        $renderers     = false;
        if ($this->columnModel !== null) {
            // TODO: optimize when the column model haven't been modified.
            $limit_columns = $this->columnModel->getColumns();
            $renderers     = $this->columnModel->getRowRenderers();
        } 
        
        //$cm = $this->getColumnModel();
        //$cm->setExcluded(array('user_id'));
        //$this->columns = $cm->getColumns();
        //var_dump($this->columns);
        //die();
        try {

            
            
            $results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);
            //$stmt = $sql->prepareStatementForSqlObject( $select );
            //$results = $stmt->execute();
            //var_dump(get_class($results));

            $r = new ResultSet($results);
            $r->setSource($this);

            if ($this->columnModel !== null) {
                $limited_columns = $this->columnModel->getColumns();

                $r->setHydratedColumns(array_keys((array) $limited_columns));
                
                $row_renderers = $this->columnModel->getRowRenderers();
                if ($row_renderers->count() > 0) {
                    $r->setRowRenderers($row_renderers);
                }
            }
            

            if ($options->hasLimit()) {
                //$row = $this->adapter->query('select FOUND_ROWS() as total_count')->execute()->current();
                $row = $this->adapter->createStatement('select FOUND_ROWS() as total_count')->execute()->current();
                $r->setTotalRows($row['total_count']);
            } else {
                $r->setTotalRows($r->count());
            }

            

            // restore result prototype
            // $this->adapter->getDriver()->registerResultPrototype($result_prototype_backup);

            // restore statement prototype
            // seems not needed in zf 2.3
            /*
            if ($is_mysqli) {
                $this->adapter->getDriver()->registerStatementPrototype($stmt_prototype_backup);
            }
            */
             

        } catch (\Exception $e) {
            // restore result prototype
            //$this->adapter->getDriver()->registerResultPrototype($result_prototype_backup);

            // seems not needed in zf 2.3
            /*
            if ($is_mysqli) {
                $this->adapter->getDriver()->registerStatementPrototype($stmt_prototype_backup);
            }
            */
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
        
        $columns = $this->getMetadataReader()->getColumnsMetadata($sql_string);
        $columnModel = new ColumnModel();
        foreach ($columns as $column => $meta) {
            $config = array(
                'definition' => $meta
            );
            $columnModel->addColumn($column, $config);
        }    
        $this->columnModel = $columnModel;
        
    }



    /**
     *
     * @return AbstractMetadataReader
     */
    public function getMetadataReader()
    {
        if ($this->metadataReader === null) {
            $this->metadataReader = $this->getDefaultMetadataReader();
        }
        return $this->metadataReader;
    }

    /**
     * 
     * @return AbstractMetadataReader
     */
    protected function getDefaultMetadataReader()
    {
        $conn = $this->adapter->getDriver()->getConnection()->getResource();
        $class = strtolower(get_class($conn));
        switch ($class) {
            case 'pdo':
                return new MetadataReader\PDOMysqlMetadataReader($conn);
            case 'mysqli':
                return new MetadataReader\MysqliMetadataReader($conn);
            default:
                throw new \Exception(__METHOD__ . " Cannot handle default metadata reader for driver '$class'");
        }

    }

    /**
     * Return the query string that was executed
     * @return string
     */
    public function getQueryString()
    {
        if ($this->query_string == '') {
            throw new Exception\InvalidUsageException(__METHOD__ . ": Invalid usage, getQueryString must be called after data has been loaded (performance reason).");
        }
        return str_replace("\n", ' ', $this->query_string);
    }


}
