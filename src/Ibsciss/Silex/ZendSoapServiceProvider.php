<?php
namespace Ibsciss\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Zend\Soap;

class ZendSoapServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['soap.wsdl'] = (isset($app['soap.wsdl'])) ? $app['soap.wsdl'] : null;

        $app['soap.clients'] = $app->share(function($app){
            return $app['soap.instances.container']['clients'];
        });
        
        $app['soap.servers'] = $app->share(function($app){
            return $app['soap.instances.container']['servers'];
        });
        
        $app['soap.client'] = $app['zend_soap.client'] = $app->share(function($app){
            return new Soap\Client($app['soap.wsdl']);
        });
        
        $app['soap.server'] = $app['zend_soap.server'] = $app->share(function($app){
            return new Soap\Server($app['soap.wsdl']);
        });
        
        $app['soap.instances.container'] = $app->share(function($app){
            
            $container_server = new \Pimple();
            $container_client = new \Pimple();
            
            if(!isset($app['soap.instances'])){
                $app['soap.instances'] = array('default' => array('soap.wsdl' => $app['soap.wsdl']));
            }
            
            foreach($app['soap.instances'] as $name => $value){
                
                $name = (is_array($value)) ? $name : $value;
                $wsdl = (isset($value['soap.wsdl'])) ? $value['soap.wsdl'] : null;
                
                $container_client[$name] = $container_client->share(function() use ($wsdl){
                    return new Soap\Client($wsdl);
                });
                
                $container_server[$name] = $container_server->share(function() use ($wsdl){
                    return new Soap\Server($wsdl);
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