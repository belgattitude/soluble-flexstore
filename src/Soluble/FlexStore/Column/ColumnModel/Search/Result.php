<?php

namespace Soluble\FlexStore\Column\ColumnModel\Search;

use Soluble\FlexStore\Column\Column;
use Soluble\FlexStore\Column\ColumnSettableInterface;
use Soluble\FlexStore\Column\Exception;
use Soluble\FlexStore\Formatter\FormatterInterface;
use ArrayObject;

class Result implements ColumnSettableInterface
{
    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    /**
     *
     * @var array
     */
    protected $results;

    public function __construct(array $results, ArrayObject $columns)
    {
        $this->columns = $columns;
        $this->results = $results;
    }

    /**
     *
     * @param FormatterInterface $formatter
     * @return Result
     */
    public function setFormatter(FormatterInterface $formatter)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setFormatter($formatter);
        }
        return $this;
    }


    /**
     *
     * @throws Exception\InvalidArgumentException
     * @param string|\Soluble\FlexStore\Column\Type\AbstractType $type
     * @return Result
     */
    public function setType($type)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setType($type);
        }
        return $this;
    }


    /**
     *
     * @param boolean $virtual
     * @return Result
     */
    public function setVirtual($virtual = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setVirtual($virtual);
        }
        return $this;
    }

    /**
     *
     * @param boolean $excluded
     * @return Result
     */
    public function setExcluded($excluded = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setExcluded($excluded);
        }
        return $this;
    }

    /**
     *
     * @param boolean $editable
     * @return Result
     */
    public function setEditable($editable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setEditable($editable);
        }
        return $this;
    }

    /**
     *
     * @param boolean $hidden
     * @return Result
     */
    public function setHidden($hidden = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setHidden($hidden);
        }
        return $this;
    }

    /**
     *
     * @param boolean $sortable
     * @return Result
     */
    public function setSortable($sortable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setSortable($sortable);
        }
        return $this;
    }

    /**
     *
     * @param boolean $groupable
     * @return Result
     */
    public function setGroupable($groupable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setGroupable($groupable);
        }
        return $this;
    }

    /**
     *
     * @param boolean $filterable
     * @return Result
     */
    public function setFilterable($filterable = true)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setFilterable($filterable);
        }
        return $this;
    }

    /**
     * Set recommended width for the column
     *
     * @throws Exception\InvalidArgumentException
     * @param float|int|string $width
     * @return Result
     */
    public function setWidth($width)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setWidth($width);
        }
        return $this;
    }

    /**
     * Set recommended horizontal align 
     *
     * @throws Exception\InvalidArgumentException
     * @param string $align can be left|center|right
     * @return Column
     */
    public function setAlign($align)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setAlign($align);
        }
    }

    /**
     * Set recommended css class
     *
     * @throws Exception\InvalidArgumentException
     * @param string $class css class
     * @return Column
     */
    public function setClass($class)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setClass($class);
        }
    }


    /**
     * Set table header for this column
     *
     * @throws Exception\InvalidArgumentException
     * @param string|null $header
     * @return Result
     */
    public function setHeader($header)
    {
        foreach ($this->results as $name) {
            $this->columns->offsetGet($name)->setHeader($header);
        }
        return $this;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return $this->results;
    }
}
