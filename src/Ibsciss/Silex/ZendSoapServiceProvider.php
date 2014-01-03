<?php
namespace Ibsciss\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Zend\Soap;

class ZendSoapServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        //define options
        $app['soap.wsdl'] = (isset($app['soap.wsdl'])) ? $app['soap.wsdl'] : null;
        $app['soap.dotNet'] = (isset($app['soap.dotNet'])) ? $app['soap.dotNet'] : false;
        $app['soap.server.class'] = (isset($app['soap.server.class'])) ? $app['soap.server.class'] : '\Zend\Soap\Server';
        $app['soap.client.class'] = $app->share(function($app){
            return ($app['soap.dotNet']) ? '\Zend\Soap\Client\DotNet' : '\Zend\Soap\Client';
        });
        
        
        $app['soap.clients'] = $app->share(function($app){
            return $app['soap.instances.container']['clients'];
        });
        
        $app['soap.servers'] = $app->share(function($app){
            return $app['soap.instances.container']['servers'];
        });
        
        $app['soap.client'] = $app['zend_soap.client'] = $app->share(function($app){
            $clients = $app['soap.instances.container']['clients'];
            return $clients[$app['soap.instances.default']];
        });
        
        $app['soap.server'] = $app['zend_soap.server'] = $app->share(function($app){
            $servers = $app['soap.instances.container']['servers'];
            return $servers[$app['soap.instances.default']];
        });
        
        $app['soap.instances.container'] = $app->share(function($app){
            
            $container_server = new \Pimple();
            $container_client = new \Pimple();
            
            if(!isset($app['soap.instances'])){
                $app['soap.instances'] = array('default');
            }
            
            foreach($app['soap.instances'] as $name => $value){
                
                $name = (is_array($value)) ? $name : $value;
                $wsdl = (isset($value['wsdl'])) ? $value['wsdl'] : $app['soap.wsdl'];
                
                $container_client[$name] = $container_client->share(function() use ($wsdl, $app, $value){
                    $defaultClass = (isset($value['dotNet']) && $value['dotNet']) ? '\Zend\Soap\Client\DotNet' : $app['soap.client.class'];
                    $class = (isset($value['client.class'])) ? $value['client.class'] : $defaultClass;
                    return new $class($wsdl);
                });
                
                $container_server[$name] = $container_server->share(function() use ($wsdl, $app, $value){
                    $class = (isset($value['server.class'])) ? $value['server.class'] : $app['soap.server.class'];
                    return new $class($wsdl);
                });
                
                if(!isset($app['soap.instances.default'])){
                    $app['soap.instances.default'] = $name;
                }
            }
            
            return array(
                'servers' => $container_server,
                'clients' => $container_client
            );
        });
        
        
    }
    
    public function boot(Application $app){}
}