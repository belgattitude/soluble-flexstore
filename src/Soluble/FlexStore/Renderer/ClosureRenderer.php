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

namespace Soluble\FlexStore\Renderer;

use ArrayObject;
use Closure;

/**
 * @method void closure(ArrayObject $row)
 */
class ClosureRenderer implements RowRendererInterface
{
    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $required_columns;

    /**
     * @param Closure $closure
     */
    public function __construct(Closure $closure)
    {
        $this->required_columns = [];
        $this->closure = $closure;
    }

    /**
     * Magic callable.
     *
     * @param string $method
     * @param array  $args
     */
    public function __call($method, $args)
    {
        if (is_callable([$this, $method])) {
            return call_user_func_array($this->$method, $args);
        }
    }

    /**
     * @param ArrayObject
     *
     * @return string
     */
    public function apply(\ArrayObject $row)
    {
        $this->closure($row);
    }

    /**
     * Return the list of columns required in order to use this renderer.
     *
     * @return array
     */
    public function getRequiredColumns()
    {
        return $this->required_columns;
    }

    /**
     * @param array $required_columns
     */
    public function setRequiredColumns(array $required_columns)
    {
        $this->required_columns = $required_columns;
    }
}
