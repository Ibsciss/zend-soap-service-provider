<?php
namespace Ibsciss\Silex\Provider;

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
        $app['soap.client.class'] = (isset($app['soap.client.class'])) ? $app['soap.client.class'] : '\Zend\Soap\Client';
        $app['soap.client.dotNet.class'] = (isset($app['soap.client.dotNet.class'])) ? $app['soap.client.dotNet.class'] : '\Zend\Soap\Client\DotNet';
        $app['soap.version'] = (isset($app['soap.version'])) ? $app['soap.version'] : null;

        //define shortcut
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

        //setup instances container
        $app['soap.instances.container'] = $app->share(function($app){

            $container_server = new \Pimple();
            $container_client = new \Pimple();

            if(!isset($app['soap.instances'])){
                $app['soap.instances'] = array('default');
            }

            foreach($app['soap.instances'] as $name => $instanceConfig){

                //php5.3 compatibility, see php.net/manual/en/function.isset.php#refsect1-function.isset-examples "isset on string offset"
                if(!is_array($instanceConfig)){
                    $name = $instanceConfig;
                    $instanceConfig = array();
                }

                $wsdl = (isset($instanceConfig['wsdl'])) ? $instanceConfig['wsdl'] : $app['soap.wsdl'];
                $version = (isset($instanceConfig['version'])) ? $instanceConfig['version'] : $app['soap.version'];
                $options = array();
                if(!is_null($version)) $options['soap_version'] = $version;

                $container_client[$name] = $container_client->share(function() use ($wsdl, $app, $instanceConfig, $options){
                    //dotNet mode
                    if($app['soap.dotNet'] || (isset($instanceConfig['dotNet']) && $instanceConfig['dotNet'])){
                        $class = (isset($instanceConfig['client.dotNet.class'])) ? $instanceConfig['client.dotNet.class'] : $app['soap.client.dotNet.class'];
                    }else{
                        $class = (isset($instanceConfig['client.class'])) ? $instanceConfig['client.class'] : $app['soap.client.class'];
                    }
                    return new $class($wsdl, $options);
                });

                $container_server[$name] = $container_server->share(function() use ($wsdl, $app, $instanceConfig, $options){
                    $class = (isset($instanceConfig['server.class'])) ? $instanceConfig['server.class'] : $app['soap.server.class'];
                    return new $class($wsdl, $options);
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