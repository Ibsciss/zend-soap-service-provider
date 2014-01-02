<?php

namespace Ibsciss\Silex\Tests;

use Silex\Application;
use Ibsciss\Silex\ZendSoapServiceProvider;

/**
 * Zend Soap Tests Cases
 */
class ZendSoapServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testIfClientisLoadedInContainer()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.client']);
        $this->assertSame($app['zend_soap.client'], $app['soap.client']);
    }
    
    public function testIfServerisLoadedInContainer()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.server']);
        $this->assertSame($app['zend_soap.server'], $app['soap.server']);
    }
    
    public function testIfWsdlIsLoadedDuringRegister()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.wsdl' => '<wsdl></wsdl>'
        ));
        $this->assertEquals($app['zend_soap.client']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['zend_soap.server']->getWsdl(), '<wsdl></wsdl>');
    }
    
    public function testIfWsdlIsLoadedNotDuringRegister()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());        
        $app['soap.wsdl'] = '<wsdl></wsdl>';
        $this->assertEquals($app['zend_soap.client']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['zend_soap.server']->getWsdl(), '<wsdl></wsdl>');
    }
    
    public function testMultipleInstanceSupportDuringRegister()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.instances' => array(
                'connexion_one',
                'connexion_two'
            )
        ));
        
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connexion_one']);
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connexion_two']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connexion_one']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connexion_two']);
        $this->assertNotSame($app['soap.servers']['connexion_two'], $app['soap.servers']['connexion_one']);
        $this->assertNotSame($app['soap.clients']['connexion_two'], $app['soap.clients']['connexion_one']);
    }
    
    public function testMultipleInstanceSupportNotDuringRegister()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());
        $app['soap.instances'] = array(
            'connexion_one',
            'connexion_two'
        );
        
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connexion_one']);
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connexion_two']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connexion_one']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connexion_two']);
        $this->assertNotSame($app['soap.servers']['connexion_two'], $app['soap.servers']['connexion_one']);
        $this->assertNotSame($app['soap.clients']['connexion_two'], $app['soap.clients']['connexion_one']);
    }
    
    public function testIfWsdlIsLoadedInMultipleInstance()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.instances' => array(
                'connexion_one' => array('soap.wsdl' => '<wsdl>one</wsdl>'),
                'connexion_two' => array('soap.wsdl' => '<wsdl>two</wsdl>')
            )
        ));
        
        $this->assertEquals($app['soap.clients']['connexion_one']->getWsdl(), '<wsdl>one</wsdl>');
        $this->assertEquals($app['soap.clients']['connexion_two']->getWsdl(), '<wsdl>two</wsdl>');
        $this->assertEquals($app['soap.servers']['connexion_one']->getWsdl(), '<wsdl>one</wsdl>');
        $this->assertEquals($app['soap.servers']['connexion_two']->getWsdl(), '<wsdl>two</wsdl>');
    }
}

?>
