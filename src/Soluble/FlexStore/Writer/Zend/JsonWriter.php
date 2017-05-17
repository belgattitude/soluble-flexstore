<?php

/*
 * soluble-flexstore library
 *
 * @author    Vanvelthem Sébastien
 * @link      https://github.com/belgattitude/soluble-flexstore
 * @copyright Copyright (c) 20016-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-flexstore/blob/master/LICENSE.md
 *
 */

namespace Soluble\FlexStore\Writer\Zend;

use Soluble\FlexStore\Writer\Http\SimpleHeaders;
use Zend\Json\Encoder;
use Soluble\FlexStore\Writer\AbstractSendableWriter;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\Options;

class JsonWriter extends AbstractSendableWriter
{
    /**
     * @var SimpleHeaders
     */
    protected $headers;

    /**
     * @param Options $options
     *
     * @return string
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            // Take store global/default options
            $options = $this->store->getOptions();
            // By default formatters are disabled in JSON
            $options->getHydrationOptions()->disableFormatters();
        }

        $data = $this->store->getData($options);
        $d = [
            'success' => true,
            'total' => $data->getTotalRows(),
            'start' => $data->getSource()->getOptions()->getOffset(),
            'limit' => $data->getSource()->getOptions()->getLimit(),
            'data' => $data->toArray()
        ];

        if ($this->options['debug']) {
            $source = $data->getSource();
            if ($source instanceof QueryableSourceInterface) {
                $d['query'] = $source->getQueryString();
            }
        }

        return Encoder::encode($d);
    }

    /**
     * Return default headers for sending store data via http.
     *
     * @return SimpleHeaders
     */
    public function getHttpHeaders()
    {
        if ($this->headers === null) {
            $this->headers = new SimpleHeaders();
            $this->headers->setContentType('application/json', 'utf-8');
            //$this->headers->setContentDispositionType(SimpleHeaders::DIPOSITION_ATTACHEMENT);
        }

        return $this->headers;
    }
}
