<?php

namespace Soluble\FlexStore\Writer;

use Soluble\FlexStore\Source\SourceInterface;
use Soluble\FlexStore\Writer\Http\SimpleHeaders;
use DateTime;
use Traversable;
use SimpleXMLElement;

class SimpleXmlWriter extends AbstractSendableWriter
{

    /**
     *
     * @var SimpleHeaders
     */
    protected $headers;
    protected $php_54_compatibility = true;

    /**
     * @var array
     */
    protected $options = array(
        /**
         * XML tag for response
         */
        'body_tag' => 'response',
        /**
         * XML tag for rows
         */
        'row_tag' => 'row',
        'encoding' => 'UTF-8'
    );

    /**
     *
     * @param SourceInterface|null $source
     * @param array|Traversable|null $options
     */
    public function __construct(SourceInterface $source = null, $options = null)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->php_54_compatibility = false;
        };

        parent::__construct($source, $options);
    }

    /**
     *
     * @param string $row_tag
     * @return SimpleXmlWriter
     */
    public function setRowTag($row_tag)
    {
        $this->options['row_tag'] = $row_tag;
        return $this;
    }

    /**
     *
     * @param string $body_tag
     * @return SimpleXmlWriter
     */
    public function setBodyTag($body_tag)
    {
        $this->options['body_tag'] = $body_tag;
        return $this;
    }

    /**
     *
     * @return string xml encoded data
     */
    public function getData()
    {
        $data = $this->source->getData();
        $bt = $this->options['body_tag'];
        $encoding = $this->options['encoding'];
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"$encoding\" ?><$bt></$bt>");

        $now = new DateTime();
        $d = array(
            'success' => true,
            'timestamp' => $now->format(DateTime::W3C),
            'total' => $data->getTotalRows(),
            'start' => $data->getSource()->getOptions()->getOffset(),
            'limit' => $data->getSource()->getOptions()->getLimit(),
            'data' => $data->toArray(),
        );

        if ($this->options['debug']) {
            $d['query'] = $data->getSource()->getQueryString();
        }
        $this->createXmlNode($d, $xml);


        return $xml->asXML();
    }

    /**
     *
     * @param array $result
     * @param SimpleXMLElement $xml
     */
    protected function createXmlNode($result, SimpleXMLElement $xml)
    {
        foreach ($result as $key => $value) {

            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml->addChild("$key");
                    $this->createXmlNode($value, $subnode);
                } else {
                    $v = array($this->options['row_tag'] => $value);
                    $this->createXmlNode($v, $xml);
                }
            } else {

                if ($this->php_54_compatibility) {
                    // assuming php 5.4+
                    $encoded = htmlspecialchars($value, ENT_XML1, $this->options->encoding);
                } else {
                    $encoded = '';

                    foreach (str_split(utf8_decode(htmlspecialchars($value))) as $char) {
                        $num = ord($char);
                        if ($num > 127) {
                            $encoded .= '&#' . $num . ';';
                        } else {
                            $encoded .= $char;
                        }
                    }
                }
                $xml->addChild($key, $encoded);
            }
        }
    }

    /**
     * Return default headers for sending store data via http 
     * @return SimpleHeaders
     */
    public function getHttpHeaders()
    {
        if ($this->headers === null) {
            $this->headers = new SimpleHeaders();
            $this->headers->setContentType('application/xml', $this->options['encoding']);
            $this->headers->setContentDispositionType(SimpleHeaders::DIPOSITION_ATTACHEMENT);
        }
        return $this->headers;
    }
    

}
