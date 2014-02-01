<?php

namespace Soluble\FlexStore\ResultSet;

use Soluble\FlexStore\Source\AbstractSource;
use Soluble\FlexStore\Helper\Paginator;
use ArrayObject;

class ResultSet extends AbstractResultSet
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY  = 'array';

    /**
     * Allowed return types
     *
     * @var array
     */
    protected $allowedReturnTypes = array(
        self::TYPE_ARRAYOBJECT,
        self::TYPE_ARRAY,
    );

    /**
     * @var ArrayObject
     */
    protected $arrayObjectPrototype = null;

    /**
     * Return type to use when returning an object from the set
     *
     * @var ResultSet::TYPE_ARRAYOBJECT|ResultSet::TYPE_ARRAY
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;


    /**
     *
     * @var boolean
     */
    protected $columnsChecked = false;

    /**
     *
     * @var array
     */
    protected $columns;

    /**
     *
     * @var integer
     */
    protected $totalRows;

    /**
     * @var \Soluble\FlexStore\Source\AbstractSource
     */
    protected $source;

    /**
     * Constructor
     *
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */
    public function __construct($returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
    {
        $this->returnType = (in_array($returnType, array(self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT))) ? $returnType : self::TYPE_ARRAYOBJECT;
        if ($this->returnType === self::TYPE_ARRAYOBJECT) {
            $this->setArrayObjectPrototype(($arrayObjectPrototype) ?: new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS));
        }
    }

    /**
     *
     * @return \Soluble\FlexStore\Helper\Paginator
     */
    public function getPaginator()
    {
        if ($this->paginator === null) {
            $this->paginator = new Paginator($this->getTotalRows(),
                                             $this->getSource()->getOptions()->getLimit(),
                                             $this->getSource()->getOptions()->getOffset());
        }
        return $this->paginator;
    }



    /**
     *
     * @param \Soluble\FlexStore\Source\AbstractSource $source
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function setSource(AbstractSource $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     *
     * @return \Soluble\FlexStore\Source\AbstractSource
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Set the total rows
     * @param int $totalRows
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function setTotalRows($totalRows)
    {
        $this->totalRows = (int) $totalRows;
        return $this;
    }


    /**
     * @return int
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }





    /**
     * Set the row object prototype
     *
     * @param  ArrayObject $arrayObjectPrototype
     * @throws Exception\InvalidArgumentException
     * @return ResultSet
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        if (!is_object($arrayObjectPrototype)
            || (!$arrayObjectPrototype instanceof ArrayObject && !method_exists($arrayObjectPrototype, 'exchangeArray'))

        ) {
            throw new Exception\InvalidArgumentException('Object must be of type ArrayObject, or at least implement exchangeArray');
        }
        $this->arrayObjectPrototype = $arrayObjectPrototype;
        return $this;
    }

    /**
     * Get the row object prototype
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->arrayObjectPrototype;
    }

    /**
     * Get the return type to use when returning objects from the set
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->returnType;
    }


    /**
     *
     * @param array $columns
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function setColumns(array $columns)
    {
        $this->columnsChecked = false;
        $this->columns = $columns;
        return $this;
    }


    /**
     *
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function unsetColumns()
    {
        $this->columnsChecked = false;
        $this->columns = null;
        return $this;
    }

    /**
     *
     * @throws Exception\UnknownColumnException
     * @return array|\ArrayObject|null
     */
    public function current()
    {
        $data = parent::current();

        if ($this->columns !== null) {

            $d = new \ArrayObject();
            if (!$this->columnsChecked) {
                foreach($this->columns as $column) {
                    if (!$data->offsetExists($column)) {
                        $msg = "Column '$column' does not exists";
                        throw new Exception\UnknownColumnException($msg);
                    }
                }
                $this->columnsChecked;
            }

            foreach($this->columns as $column) {
                $d[$column] = $data[$column];
            }
            $data = $d;


        }
        if ($this->returnType === self::TYPE_ARRAYOBJECT && is_array($data)) {
            /** @var $ao ArrayObject */
            $ao = clone $this->arrayObjectPrototype;

            if ($ao instanceof ArrayObject || method_exists($ao, 'exchangeArray')) {
                $ao->exchangeArray($data);
            }
            return $ao;
        }
        return $data;
    }


}
