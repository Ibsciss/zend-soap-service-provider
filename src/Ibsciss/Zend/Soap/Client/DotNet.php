<?php

namespace Ibsciss\Zend\Soap\Client;

class DotNet extends \Zend\Soap\Client\DotNet
{
    /**
     * Perform result pre-processing
     *
     * My be overridden in descendant classes
     *
     * @param  object $result
     * @return mixed
     */
    protected function _preProcessResult($result)
    {
        $resultProperty = $this->getLastMethod() . 'Result';

        if(property_exists($result, $resultProperty)){
            return $result->$resultProperty;
        }

        return $result;
    }

}