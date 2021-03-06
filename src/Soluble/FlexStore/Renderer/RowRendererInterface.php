<?php

declare(strict_types=1);

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

interface RowRendererInterface
{
    public function apply(ArrayObject $row): void;

    /**
     * Return the list of columns in order to run the renderer.
     *
     * @return array
     */
    public function getRequiredColumns(): array;
}
