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

namespace Soluble\FlexStore;

use Soluble\FlexStore\Options\HydrationOptions;

class Options
{
    /**
     * @var HydrationOptions
     */
    protected $hydrationOptions;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int|null
     */
    protected $offset;

    /**
     * Set the (maximum) number of results to return.
     */
    public function setLimit(int $limit, ?int $offset = null): self
    {
        $this->limit = $limit;
        if ($offset !== null) {
            $this->setOffset($offset);
        }

        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Unset limit of results
     * Provides fluent interface.
     *
     * @param bool $unset_offset whether to unset offset as well
     *
     * @return Options
     */
    public function unsetLimit($unset_offset = true): self
    {
        $this->limit = null;
        if ($unset_offset) {
            $this->offset = null;
        }

        return $this;
    }

    /**
     * Tells whether the option contains a limit.
     *
     * @return bool
     */
    public function hasLimit(): bool
    {
        return $this->limit !== null;
    }

    /**
     * Tells whether the option contains an offset.
     *
     * @return bool
     */
    public function hasOffset(): bool
    {
        return $this->offset !== null;
    }

    /**
     * Set the offset (the record to start reading when using limit).
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Return the offset when using limit
     * Offset gives the record number to start reading
     * from when a paging query is in use.
     *
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Unset previously set offset.
     *
     * @return Options
     */
    public function unsetOffset()
    {
        $this->offset = null;

        return $this;
    }

    public function getHydrationOptions(): HydrationOptions
    {
        if ($this->hydrationOptions === null) {
            $this->hydrationOptions = new HydrationOptions();
        }

        return $this->hydrationOptions;
    }
}
