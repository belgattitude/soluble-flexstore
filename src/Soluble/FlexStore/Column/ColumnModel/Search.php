<?php

namespace Soluble\FlexStore\Column\ColumnModel;

use Soluble\FlexStore\Column\ColumnModel\Search\Result;
use Soluble\FlexStore\Column\Exception;
use ArrayObject;

class Search
{
    /**
     *
     * @var ArrayObject
     */
    protected $columns;

    public function __construct(ArrayObject $columns)
    {
        $this->columns = $columns;
    }

    /**
     *
     * @return Result
     */
    public function all()
    {
        return new Result(array_keys((array) $this->columns), $this->columns);
    }

    /**
     * Search ColumnModel for columns that are not in the $columns parameter
     *
     * @throws Exception\InvalidArgumentException
     * @return Result
     */
    public function notIn(array $columns)
    {
        $results = [];
        foreach ($this->columns as $name => $column) {
            if (!in_array($name, $columns)) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     *
     * @return Result
     */
    public function in(array $columns)
    {
        $results = [];
        foreach ($columns as $column) {
            $column = trim($column);
            if ($this->columns->offsetExists($column)) {
                $results[] = $column;
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     *
     * @param string $regexp
     * @return Result
     */
    public function regexp($regexp)
    {
        $results = [];
        foreach ($this->columns as $name => $column) {
            $column = trim($column);
            if (preg_match($regexp, $name)) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     *
     * @param string $type
     * @return Result
     */
    public function findByType($type)
    {
        $results = [];
        foreach ($this->columns as $name => $column) {
            if ($column->getType() == $type) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }

    /**
     * Return virtual column (not materialized by the underlying datasource)
     * @return Result
     */
    public function findVirtual()
    {
        $results = [];
        foreach ($this->columns as $name => $column) {
            if ($column->isVirtual()) {
                $results[] = $name;
            }
        }
        return new Result($results, $this->columns);
    }
}
