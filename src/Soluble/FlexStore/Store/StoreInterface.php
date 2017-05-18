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

namespace Soluble\FlexStore\Store;

interface StoreInterface
{
    /**
     * Return underlying data source.
     *
     * @return \Soluble\FlexStore\Source\SourceInterface
     */
    public function getSource();

    /**
     * Return the underlying store data as a resultset.
     *
     * @param \Soluble\FlexStore\Options $options
     *
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(\Soluble\FlexStore\Options $options = null);

    /**
     * Return column model associated with datasource.
     *
     * @return \Soluble\FlexStore\Column\ColumnModel
     */
    public function getColumnModel();

    /**
     * @return \Soluble\FlexStore\Options
     */
    public function getOptions();
}
