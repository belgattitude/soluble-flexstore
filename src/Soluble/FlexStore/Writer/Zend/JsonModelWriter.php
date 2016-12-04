<?php

namespace Soluble\FlexStore\Writer\Zend;

use Zend\View\Model\JsonModel as ZendJsonModel;
use Soluble\FlexStore\Source\QueryableSourceInterface;
use Soluble\FlexStore\Options;
use Soluble\FlexStore\Writer\AbstractWriter;

class JsonModelWriter extends AbstractWriter
{
    /**
     * @param Options $options
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getData(Options $options = null)
    {
        if ($options === null) {
            // Take store global/default options
            $options = $this->store->getOptions();
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

        $json = new ZendJsonModel($d);

        return $json;
    }
}
