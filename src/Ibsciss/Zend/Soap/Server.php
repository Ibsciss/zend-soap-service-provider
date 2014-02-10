<?php

namespace Ibsciss\Zend\Soap;

class Server extends \Zend\Soap\Server
{

    /**
     * Informs if the soap server is in debug mode
     * @var boolean
     */
    protected $debug = false;

    /**
     * catch exception during soap request
     * @var \Exception
     */
    protected $exception = null;

    /**
     * Internal SoapServer instance
     * @var SoapServer
     */
    protected $server = null;

    /**
     * Set the debug mode.
     * In debug mode, all exceptions are send to the client.
     * @param boolean $debug
     */
    public function setDebugMode($debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * Checks if provided fault name is registered as valid in this server.
     *
     * @param $fault Name of a fault class
     * @return bool
     */
    public function isRegisteredAsFaultException($fault)
    {
        return ($this->debug) ? true : parent::isRegisteredAsFaultException($fault);
    }

    public function fault($fault = null, $code = 'Receiver')
    {
        $this->exception = (is_string($fault)) ? new \Exception($fault) : $fault;
        return parent::fault($fault, $code);
    }

    public function getException()
    {
        return $this->exception;
    }

    protected function _getSoap()
    {
        if($this->server instanceOf \SoapServer)
            return $this->server;

        $this->server = parent::_getSoap();
        return $this->server;
    }

    public function getSoap()
    {
        return $this->_getSoap();
    }
}
