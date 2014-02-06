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
}
