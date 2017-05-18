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

namespace Soluble\FlexStore\ResultSet;

use ArrayObject;
//use Soluble\DbWrapper\Result\ResultInterface;
use Zend\Db\ResultSet\ResultSet as ZFResultSet;

abstract class AbstractResultSet implements ResultSetInterface
{
    const TYPE_ARRAYOBJECT = 'arrayobject';
    const TYPE_ARRAY = 'array';

    /**
     * Return type to use when returning an object from the set.
     *
     * @var string
     */
    protected $returnType = self::TYPE_ARRAYOBJECT;

    /**
     * Allowed return types.
     *
     * @var array
     */
    protected $allowedReturnTypes = [
        self::TYPE_ARRAYOBJECT,
        self::TYPE_ARRAY,
    ];

    /**
     * @var ArrayObject
     */
    protected $arrayObjectPrototype = null;

    /**
     * @var ZFResultSet
     */
    protected $zfResultSet;

    /**
     * Constructor.
     *
     * @param ZFResultSet      $resultSet
     * @param string           $returnType
     * @param null|ArrayObject $arrayObjectPrototype
     */

/*
public function __constructOld(ZFResultSet $resultSet, $returnType = self::TYPE_ARRAYOBJECT, $arrayObjectPrototype = null)
{
    $this->zfResultSet = $resultSet;
    $this->returnType = (in_array($returnType, [self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT])) ? $returnType : self::TYPE_ARRAYOBJECT;
    if ($this->returnType === self::TYPE_ARRAYOBJECT) {
        $this->setArrayObjectPrototype(($arrayObjectPrototype) ?: new ArrayObject([], ArrayObject::ARRAY_AS_PROPS));
    }
}
*/

    /**
     * Constructor.
     *
     * @param ZFResultSet $resultSet
     * @param string      $returnType
     */
    public function __construct($resultSet, $returnType = self::TYPE_ARRAYOBJECT)
    {
        $this->zfResultSet = $resultSet;
        $this->returnType = (in_array($returnType, [self::TYPE_ARRAY, self::TYPE_ARRAYOBJECT])) ? $returnType : self::TYPE_ARRAYOBJECT;
    }

    /**
     * Set the row object prototype.
     *
     * @param ArrayObject $arrayObjectPrototype
     *
     * @throws Exception\InvalidArgumentException
     *
     * @return AbstractResultSet
     */
    public function setArrayObjectPrototype($arrayObjectPrototype)
    {
        $this->zfResultSet->setArrayObjectPrototype($arrayObjectPrototype);

        return $this;
    }

    /**
     * Get the row object prototype.
     *
     * @return ArrayObject
     */
    public function getArrayObjectPrototype()
    {
        return $this->zfResultSet->getArrayObjectPrototype();
    }

    /**
     * Get the return type to use when returning objects from the set.
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->zfResultSet->getReturnType();
    }

    /**
     * @return AbstractResultSet
     */
    public function buffer()
    {
        $this->zfResultSet->buffer();

        return $this;
    }

    /**
     * @return bool
     */
    public function isBuffered()
    {
        return $this->zfResultSet->isBuffered();
    }

    /**
     * Get the data source used to create the result set.
     *
     * @return null|\Iterator
     */
    public function getDataSource()
    {
        return $this->zfResultSet->getDataSource();
    }

    /**
     * Retrieve count of fields in individual rows of the result set.
     *
     * @return int
     */
    public function getFieldCount()
    {
        return $this->zfResultSet->getFieldCount();
    }

    /**
     * Iterator: move pointer to next item.
     */
    public function next()
    {
        $this->zfResultSet->next();
    }

    /**
     * Iterator: retrieve current key.
     *
     * @return mixed
     */
    public function key()
    {
        return $this->zfResultSet->key();
    }

    /**
     * Iterator: get current item.
     *
     * @return array|\ArrayObject|null
     */
    public function current()
    {
        return $this->zfResultSet->current();
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        return $this->zfResultSet->valid();
    }

    /**
     * Iterator: rewind.
     */
    public function rewind()
    {
        $this->zfResultSet->rewind();
    }

    /**
     * Countable: return count of rows.
     *
     * @return int
     */
    public function count()
    {
        return $this->zfResultSet->count();
    }

    /**
     * Cast result set to array of arrays.
     *
     * @return array
     *
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        return $this->zfResultSet->toArray();
    }
}
