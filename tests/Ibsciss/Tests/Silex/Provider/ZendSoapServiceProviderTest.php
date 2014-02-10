<?php

namespace Ibsciss\Tests\Silex\Provider;

use Silex\Application;
use Ibsciss\Silex\Provider\ZendSoapServiceProvider;

/**
 * Zend Soap Tests Cases
 */
class ZendSoapServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testIfClientisLoadedInContainer()
    {
        $app = $this->getApplication();
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.client']);
        $this->assertSame($app['zend_soap.client'], $app['soap.client']);
    }

    public function testIfServerisLoadedInContainer()
    {
        $app = $this->getApplication();
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
        $app = $this->getApplication();
        $app['soap.wsdl'] = '<wsdl></wsdl>';
        $this->assertEquals($app['zend_soap.client']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['zend_soap.server']->getWsdl(), '<wsdl></wsdl>');
    }

    public function testMultipleInstanceSupportDuringRegister()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array('connection_one', 'connection_two');

        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connection_one']);
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connection_two']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connection_one']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connection_two']);
        $this->assertNotSame($app['soap.servers']['connection_two'], $app['soap.servers']['connection_one']);
        $this->assertNotSame($app['soap.clients']['connection_two'], $app['soap.clients']['connection_one']);
    }

    public function testMultipleInstanceSupportNotDuringRegister()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array(
            'connection_one',
            'connection_two'
        );

        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connection_one']);
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connection_two']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connection_one']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connection_two']);
        $this->assertNotSame($app['soap.servers']['connection_two'], $app['soap.servers']['connection_one']);
        $this->assertNotSame($app['soap.clients']['connection_two'], $app['soap.clients']['connection_one']);
    }

    public function testIfWsdlIsLoadedInMultipleInstance()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.wsdl' => '<wsdl></wsdl>',
            'soap.instances' => array(
                'connection_one',
                'connection_two'
            )
        ));

        $this->assertEquals($app['soap.clients']['connection_one']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['soap.clients']['connection_two']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['soap.servers']['connection_one']->getWsdl(), '<wsdl></wsdl>');
        $this->assertEquals($app['soap.servers']['connection_two']->getWsdl(), '<wsdl></wsdl>');
    }

    public function testIfDiffrentWsdlAreLoadedInMultipleInstance()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider(), array(
            'soap.instances' => array(
                'connection_one' => array('wsdl' => '<wsdl>one</wsdl>'),
                'connection_two' => array('wsdl' => '<wsdl>two</wsdl>')
            )
        ));

        $this->assertEquals($app['soap.clients']['connection_one']->getWsdl(), '<wsdl>one</wsdl>');
        $this->assertEquals($app['soap.clients']['connection_two']->getWsdl(), '<wsdl>two</wsdl>');
        $this->assertEquals($app['soap.servers']['connection_one']->getWsdl(), '<wsdl>one</wsdl>');
        $this->assertEquals($app['soap.servers']['connection_two']->getWsdl(), '<wsdl>two</wsdl>');
    }

    public function testIfFirstLoadedInstanceIsTheDefaultOne()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array(
            'connection_one' => array('wsdl' => '<wsdl>one</wsdl>'),
            'connection_two' => array('wsdl' => '<wsdl>two</wsdl>')
        );

        $this->assertSame($app['soap.clients']['connection_one'], $app['soap.client']);
        $this->assertSame($app['soap.servers']['connection_one'], $app['soap.server']);
    }

    public function testOverloadingDefaultSoapClass()
    {
        $app = $this->getApplication();
        $app['soap.server.class'] = '\stdClass';
        $app['soap.client.class'] = '\stdClass';
        $this->assertInstanceOf('\stdClass', $app['soap.client']);
        $this->assertInstanceOf('\stdClass', $app['soap.server']);
    }

    public function testOverloadingSpecificInstanceClass()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array(
            'connection_one' => array('server.class' => '\stdClass'),
            'connection_two' => array('client.class' => '\stdClass')
        );

        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connection_one']);
        $this->assertInstanceOf('stdClass', $app['soap.servers']['connection_one']);

        $this->assertInstanceOf('stdClass', $app['soap.clients']['connection_two']);
        $this->assertInstanceOf('Zend\Soap\Server', $app['soap.servers']['connection_two']);
    }

    public function testGlobalDotNetMode()
    {
        $app = $this->getApplication();
        $app['soap.dotNet'] = true;

        $this->assertInstanceOf('Zend\Soap\Client\DotNet', $app['soap.client']);
    }

    public function testDotNetModeForSpecificInstance()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array(
            'connection_one',
            'connection_two' => array('dotNet' => true)
        );
        $this->assertInstanceOf('Zend\Soap\Client', $app['soap.clients']['connection_one']);
        $this->assertInstanceOf('Zend\Soap\Client\DotNet', $app['soap.clients']['connection_two']);
    }

    public function testVersionSetForGlobalInstances()
    {
        $app = $this->getApplication();
        $app['soap.version'] = SOAP_1_1;

        $this->assertEquals($app['soap.client']->getSoapVersion(), SOAP_1_1);
        $this->assertEquals($app['soap.server']->getSoapVersion(), SOAP_1_1);

    }

    public function testVersionForSpecificInstances()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array(
            'connection_one' => array('version' => SOAP_1_1),
            'connection_two' => array('dotNet' => true),
            'connection_three'
        );

        $this->assertEquals($app['soap.clients']['connection_one']->getSoapVersion(), SOAP_1_1);
        $this->assertEquals($app['soap.servers']['connection_one']->getSoapVersion(), SOAP_1_1);

        //dotNet use 1.1 by default
        $this->assertEquals($app['soap.clients']['connection_two']->getSoapVersion(), SOAP_1_1);
        $this->assertEquals($app['soap.servers']['connection_two']->getSoapVersion(), SOAP_1_2);

        //check default config
        $this->assertEquals($app['soap.clients']['connection_three']->getSoapVersion(), SOAP_1_2);
        $this->assertEquals($app['soap.servers']['connection_three']->getSoapVersion(), SOAP_1_2);
    }

    public function testOverloadingDefaultDotNetClass()
    {
        $app = $this->getApplication();
        $app['soap.client.dotNet.class'] = '\stdClass';
        $app['soap.dotNet'] = true;
        $this->assertInstanceOf('\stdClass', $app['soap.client']);
    }

    public function testOverloadingSpecificInstanceDotNetClass()
    {
        $app = $this->getApplication();
        $app['soap.instances'] = array(
            'connection_one' => array(
                'dotNet' => true,
                'client.dotNet.class' => '\stdClass'
            ),
            'connection_two' => array('dotNet' => true),
            'connection_three'
        );

        $this->assertInstanceOf('\stdClass', $app['soap.clients']['connection_one']);
        $this->assertInstanceOf('\Zend\Soap\Client\DotNet', $app['soap.clients']['connection_two']);
        $this->assertInstanceOf('\Zend\Soap\Client', $app['soap.clients']['connection_three']);

    }

    public function testServerDebugMode()
    {
        $app = $this->getApplication();

        $beforeDebug = $app['soap.server']->fault(new \Exception('test'));
        $app['soap.server']->setDebugMode();
        $afterDebug = $app['soap.server']->fault(new \Exception('test'));

        $this->assertEquals('Unknown error', $beforeDebug->getMessage());
        $this->assertEquals('test', $afterDebug->getMessage());
    }

    public function testAutoEnableDebugMode()
    {
        $app = $this->getApplication();
        $app['debug'] = true;

        $afterDebug = $app['soap.server']->fault(new \Exception('test'));

        $this->assertEquals('test', $afterDebug->getMessage());
    }

    public function testDotNetClientDefaultOverride()
    {
        $app = $this->getApplication();
        $app['soap.dotNet'] = true;

        $this->assertInstanceOf('\Ibsciss\Zend\Soap\Client\DotNet', $app['soap.client']);
    }

    public function test_preProcessResult()
    {
        $app = $this->getApplication();
        $app['soap.dotNet'] = true;
        $app['soap.client.dotNet.class'] = '\Ibsciss\Mocks\Zend\Soap\Client\DotNet';
        $client = $app['soap.client'];
        $client->setLastMethod('testCall');

        //set protected method public
        $reflection = new \ReflectionClass(get_class($client));
        $method = $reflection->getMethod('_preProcessResult');
        $method->setAccessible(true);

        $parameters = new \stdClass();
        $parameters->test = 'test';
        $parametersBag = array($parameters);

        $result = $method->invokeArgs($client, $parametersBag);
        $this->assertEquals($parameters, $result);

        $parameters->testCallResult = new \stdClass();
        $parameters->testCallResult->test = 'test';
        $parametersBag = array($parameters);

        $result = $method->invokeArgs($client, $parametersBag);
        $this->assertEquals($parameters->testCallResult, $result);
    }

    public function testExceptionOverride()
    {
        $app = $this->getApplication();
        $server = $app['soap.server'];
        $fault = $server->fault(new \Exception('test'));

        $exception = $server->getException();
        $this->assertInstanceOf('\Exception', $exception);
        $this->assertEquals('test', $exception->getMessage());

        $this->assertInstanceOf('\SoapFault', $fault);
        $this->assertEquals('Unknown error', $fault->getMessage());
    }

    public function testSoapServerObjectAccess()
    {
        $app = $this->getApplication();
        $server = $app['soap.server'];
        $server->setOptions(array('location'=>'test://', 'uri'=>'http://framework.zend.com'));
        $internalServer = $server->getSoap();
        $this->assertInstanceOf('\SoapServer', $internalServer);
        $this->assertSame($internalServer, $server->getSoap());
    }

    public function getApplication()
    {
        $app = new Application();
        $app->register(new ZendSoapServiceProvider());

        return $app;
    }
}

?>
