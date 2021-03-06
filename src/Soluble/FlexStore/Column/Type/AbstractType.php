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

namespace Soluble\FlexStore\Column\Type;

abstract class AbstractType
{
    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
