<?php

namespace Ibsciss\Mocks\Zend\Soap\Client;

class DotNet extends \Ibsciss\Zend\Soap\Client\DotNet
{

    public function setLastMethod($lastMethod)
    {
        $this->lastMethod = $lastMethod;
    }

}
