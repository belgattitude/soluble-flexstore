<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;

abstract class AbstractSendableWriter extends AbstractWriter implements HttpSendableInterface
{

    abstract public function getHttpHeaders();
    
    /**
     * Send the store data via http
     * 
     * @param SimpleHeaders $headers
     * @param boolean $die_after
     */
    public function send(SimpleHeaders $headers=null, $die_after=true) {
        
        if ($headers === null) {
            $headers = $this->getHttpHeaders();
        }
        ob_end_clean();
        $headers->outputHeaders($die_after);
        echo $this->getData();
    }
            
    
    
}