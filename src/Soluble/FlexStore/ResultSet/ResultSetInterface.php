<?php

namespace Soluble\FlexStore\ResultSet;

use Countable;
use Traversable;
use Iterator;

interface ResultSetInterface extends Traversable, Countable, Iterator
{
    /**
     * Field terminology is more correct as information coming back
     * from the database might be a column, and/or the result of an
     * operation or intersection of some data
     *
     * @return mixed
     */
    public function getFieldCount();

    /**
     * Cast result set to array of arrays
     *
     * @return array
     */
    public function toArray();
}
