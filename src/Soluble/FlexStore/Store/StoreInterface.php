<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Soluble\FlexStore\Store;

interface StoreInterface
{
    /**
     * Return underlying data source
     * 
     * @return \Soluble\FlexStore\Source\SourceInterface
     */
    public function getSource();

    /**
     * Return the underlying store data as a resultset
     *
     * @param \Soluble\FlexStore\Options $options
     * @return \Soluble\FlexStore\ResultSet\ResultSet
     */
    public function getData(\Soluble\FlexStore\Options $options = null);

    /**
     * Return column model associated with datasource
     * @return \Soluble\FlexStore\Column\ColumnModel
     */
    public function getColumnModel();
}
