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
            'soap.wsdl' => '<wsdl></wsdl>',
            'soap.instances' => array(
                'connexion_one',
                'connexion_two'
            )
        ));
        
        $this->assertEquals($app['soap.clients']['connexion_one']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['soap.clients']['connexion_two']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['soap.servers']['connexion_one']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['soap.servers']['connexion_two']->getWsdl(), '<wsdl></wsdl>');
    }
    
    public function testIfDiffrentWsdlAreLoadedInMultipleInstance()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.instances' => array(
                'connexion_one' => array('wsdl' => '<wsdl>one</wsdl>'),
                'connexion_two' => array('wsdl' => '<wsdl>two</wsdl>')
            )
        ));
        
        $this->assertEquals($app['soap.clients']['connexion_one']->getWsdl(), '<wsdl>one</wsdl>');
        $this->assertEquals($app['soap.clients']['connexion_two']->getWsdl(), '<wsdl>two</wsdl>');
        $this->assertEquals($app['soap.servers']['connexion_one']->getWsdl(), '<wsdl>one</wsdl>');
        $this->assertEquals($app['soap.servers']['connexion_two']->getWsdl(), '<wsdl>two</wsdl>');
    }
    
    public function testIfFirstLoadedInstanceIsTheDefaultOne()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.instances' => array(
                'connexion_one' => array('wsdl' => '<wsdl>one</wsdl>'),
                'connexion_two' => array('wsdl' => '<wsdl>two</wsdl>')
            )
        ));
        
        $this->assertSame($app['soap.clients']['connexion_one'], $app['soap.client']);
        $this->assertSame($app['soap.servers']['connexion_one'], $app['soap.server']);
    }
    
    public function testOverloadingDefaultSoapClass()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());
        $app['soap.server.class'] = '\stdClass';
        $app['soap.client.class'] = '\stdClass';
        $this->assertInstanceOf('\stdClass', $app['soap.client']);
        $this->assertInstanceOf('\stdClass', $app['soap.server']);
    }
    
    public function testOverloadingSpecificInstanceClass()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());
        $app['soap.instances'] = array(
            'connexion_one' => array('server.class' => '\stdClass'),
            'connexion_two' => array('client.class' => '\stdClass')
        );
        
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connexion_one']);
        $this->assertInstanceOf('stdClass', $app['soap.servers']['connexion_one']);

        $this->assertInstanceOf('stdClass', $app['soap.clients']['connexion_two']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connexion_two']);
    }
    
    public function testGlobalDotNetMode()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());
        $app['soap.dotNet'] = true;
        
        $this->assertInstanceOf('Zend\Soap\Client\DotNet', $app['soap.client']);
    }
    
    public function testDotNetModeForSpecificInstance()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.instances' => array(
                'connexion_one',
                'connexion_two' => array('dotNet' => true)
            )
        ));
        
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connexion_one']);
        $this->assertInstanceOf('Zend\Soap\Client\DotNet', $app['soap.clients']['connexion_two']);
    }
}

?>
