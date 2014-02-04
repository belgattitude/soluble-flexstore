<?php

namespace Soluble\FlexStore\Metadata\Source;
abstract class AbstractMetadataSource
{
    
    /**
     * Keep static cache in memory
     * @var boolean
     */
    protected $cache_active = true;

    /**
     * 
     * @param boolean $active
     * @return \Soluble\FlexStore\Metadata\Source\AbstractMetadataSource
     */
    public function setStaticCache($active=true)
    {
        $this->cache_active = $active;
        return $this;
    }
    
    /**
     * Return
     *
     * @param string $sql
     * @return \ArrayObject
     * @throws Exception\UnsupportedDatatypeException
     * @throws Exception\AmbiguousColumnException
     */
    public function getColumnsMetadata($sql)
    {


        if ($this->cache_active) {
            $cache_key = md5($sql);

            if (!array_key_exists($cache_key, static::$metadata_cache)) {
                $md = $this->readColumnsMetadata($sql);
                static::$metadata_cache = $md;
            }

            return static::$metadata_cache;

        } else {
            return $this->readColumnsMetadata($sql);
        }

    }


    /**
     *
     * @param string $sql
     * @return \ArrayObject
     * @throws Exception\UnsupportedDatatypeException
     * @throws Exception\AmbiguousColumnException
     */
    abstract protected function readColumnsMetadata($sql);



    /**
     * Optimization, will add false condition to the query
     * so the metadata loading will be faster
     *
     *
     * @param string $sql query string
     * @return string
     */
    protected function makeQueryEmpty($sql)
    {
        // see the reason why in Vision_Store_Adapter_ZendDbSelect::getMetatData
        //$sql = str_replace("('__innerselect'='__innerselect')", '(1=0)', $sql);

        $sql = preg_replace('/(\r\n|\r|\n|\t)+/', " ", strtolower($sql));
        $sql = preg_replace('/\s+/', ' ', $sql);

        return $sql;
    }

}