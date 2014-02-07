zend-soap-service-provider
==========================

A soap service provider for Silex, based on the ZendSoap component from ZendFramework project.

[![Build Status](https://travis-ci.org/Ibsciss/zend-soap-service-provider.png?branch=master)](https://travis-ci.org/Ibsciss/zend-soap-service-provider) [![Coverage Status](https://coveralls.io/repos/Ibsciss/zend-soap-service-provider/badge.png?branch=master)](https://coveralls.io/r/Ibsciss/zend-soap-service-provider?branch=master)


For more informations about Zend Soap, check the Zend Framework documentation :
* [Zend Soap Server](http://framework.zend.com/manual/2.2/en/modules/zend.soap.server.html)
* [Zend Soap Client](http://framework.zend.com/manual/2.2/en/modules/zend.soap.client.html)

##Why a Zend Soap silex service provider ?

* For testing
* For a better integration
* For simplicity

##Install

1. Add `"ibsciss/zend-soap-service-provider": "dev-master"` in the require section of your `composer.json` and run the `composer install` command.
2. Register service : `$app->register(new ZendSoapServiceProvider());` and don't forget the `use \Ibsciss\Silex\Provider\ZendSoapServiceProvider` statement.

##Usages

###Basic usages

When the service provider is registered, you have access to the two basic services :
* **soap.server**, instance of Zend\Soap\Server`
* **soap.client**, instance of Zend\Soap\Client`

```php
$app = new Application();
$app->register(new ZendSoapServiceProvider());

//client method call
$app['soap.client']->methodCall();

//server handling
$app['soap.server']->handle();
```

###Multiple instances

If you need more connection, you can define several instances using `soap.instances` parameters.

```php
//during registration
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connection_one',
        'connection_two'
    )
));

// --- OR ---
$app->register(new ZendSoapServiceProvider());
$app['soap.instances'] = array(
    'connection_one',
    'connection_two'
);
```

You have access to you instances with the two services :
* `soap.clients`
* `soap.servers`

*The first defined service is the default one and became accessible from `soap.client` and `soap.server` services.*

```php
$app['soap.clients']['connection_one']; //same as $app['soap.client'];
$app['soap.servers']['connection_two'];
```

###WSDL management

You can provide a (optional) WSDL for the global service with the `soap.wsdl` parameter.

```php
//during registration
$app->register(new ZendSoapServiceProvider(), array(
    'soap.wsdl' => '<wsdl></wsdl>';
));
// --- OR ---
$app['soap.wsdl'] = '<wsdl></wsdl>';

$app['soap.server']->getWsdl(); //return <wsdl></wsdl>
```

For multiple instances, its possible to define wsdl for a specific instance :

```php
//during registration
$app->register(new ZendSoapServiceProvider(), array(
    'soap.wsdl' => '<wsdl></wsdl>',
    'soap.instances' => array(
        'connection_one',
        'connection_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
    );
));

// --- OR ---
$app['soap.wsdl'] = '<wsdl></wsdl>';
$app['soap.instances'] = array(
    'connection_one'
    'connection_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
);

```

**Note :** if you provide one wsdl per instance you don't have to specify a global one

```php
//if you provide one wsdl per instance you don't have to specify a global one
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connection_one' => array('wsdl' => '<wsdl></wsdl>'),
        'connection_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
    );
));

$app['soap.servers']['connection_one']->getWsdl() //return <wsdl></wsdl>
$app['soap.servers']['connection_two']->getWsdl() //return <wsdl>anotherOne</wsdl>
```

## Differences with the \Zend\Soap implementation

There is two minor change beetween the original `\Zend\Soap` package and the `ZendSoapServiceProvider` provided packages :

### In `\Zend\Soap\Client\DotNet`

A condition is added in the `_preProcessResult` method: the dotNet soap implementation send the result in a `[LastRequest]Result` xml node, so the DotNet _preProcessResult return directly this node.

But in some case, when the method return anythings and if this behavior is not defined in the ws definition (wsdl for example), this behavior can rise an notice error because the searched node does not exists.

So the serviceProvider extends the `DotNet` class to add a `exists` condition on the `[LastRequest]Result` XML node to avoid this error.

### In `\Zend\Soap\Server`

When an exception is raised in your code the `\Zend\Soap\Server` catch it and check if this is an authorized exception. If not, and for security reason, it send an "Unknow error" message. And if in production its a sane behavior, its really annoying during development & tests process.

So the serviceProvider extends the `Server` class to add a `debugMode` method which is automatically activated when the silex `debug` options is `true` (manual enable/disable debug mode is provide with the `setDebugMode($boolean)` server method).
Example :

```php
//enable:
$app['soap.server']->setDebugMode();

//disable:
$app['soap.server']->setDebugMode(false);
````

**In debug mode the Server send all exceptions to the soap client.**

##Advanced topic

###Change Soap class

If you want to use your own personal soap class, or for test purpose. You can override the soap, server or client, class with the `soap.client.class` and `soap.server.class`.

**Warning:** If you are in dotNet mode, you have to use `soap.client.dotNet.class` (or `client.dotNet.class` for an instance override).

```php
//global level
$app->register(new ZendSoapServiceProvider());

$app['soap.server.class'] = '\stdClass';
$app['soap.client.class'] = '\stdClass';

$app['soap.client']; //instanceOf stdClass;
$app['soap.server']; //instanceOf stdClass;

//----------------
//you can also override at the instance scope
$app = new Application();
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connection_one' => array('server.class' => '\stdClass'),
        'connection_two' => array('client.class' => '\stdClass')
    )
));

$app['soap.clients']['connection_one']; //instanceOf Zend\Soap\Client
$app['soap.servers']['connection_one']; //instanceOf stdClass

$app['soap.clients']['connection_two']; //instanceOf stdClass
$app['soap.servers']['connection_two']; //instanceOf Zend\Soap\Server
```

###Change Soap version

You are able to specify the soap version using the `soap.version` attribute.
The allowed values are :

* SOAP_1_1
* SOAP_1_2 (default value)

```php
    $app->register(new ZendSoapServiceProvider(), array(
        'soap.version' => SOAP_1_1
    ));

    // ----- OR -----

    $app['soap.version'] = SOAP_1_1;

    //results :
    $app['soap.client']->getSoapVersion(); // SOAP_1_1;
    $app['soap.server']->getSoapVersion(); // SOAP_1_1;

    // -----------------------
    //like others options, you can define it at the instance level :

    $app->register(new ZendSoapServiceProvider(), array(
        'soap.instances' => array(
            'connection_one' => array('version' => SOAP_1_1),
            'connection_two' => array('dotNet' => true),
            'connection_three'
        )
    ));

    $app['soap.clients']['connection_one']->getSoapVersion(); // SOAP_1_1
    $app['soap.servers']['connection_one']->getSoapVersion(); // SOAP_1_1

    //dotNet use 1.1 by default;
    $app['soap.clients']['connection_two']->getSoapVersion(); // SOAP_1_1
    $app['soap.servers']['connection_two']->getSoapVersion(); // SOAP_1_2

    //default config
    $app['soap.clients']['connection_three']->getSoapVersion(); // SOAP_1_2
    $app['soap.servers']['connection_three']->getSoapVersion(); // SOAP_1_2
```

###DotNet specific mode

The dotNet framework process soap parameters a little different than PHP or Java implementations.

So, if you have to integrate your soap web services with a dotNet server, set the `soap.dotNet` option at `true`.

```php
$app['soap.dotNet'] = true;
$app['soap.client'] // instanceOf Zend\Soap\Client\DotNet

//you can also define it at the instance scope
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connection_one' => array('dotNet' => true),
        'connection_two'
    )
));

$app['soap.clients']['connection_one']; //instanceOf Zend\Soap\Client\DotNet
$app['soap.clients']['connection_two']; //instanceOf Zend\Soap\Client
```

*If you want to override the dotNet class, use the `soap.client.dotNet.class` attribute instead of `soap.client.class`.*

##Summary

###Services

* **soap.client** : default soap client instance, alias of the first defined instances
* **soap.server** : default soap server instance, alias of the first defined instances
* **soap.clients** : soap clients instances container
* **soap.servers** : soap servers instances container

###parameters

* **soap.wsdl** : global wsdl
* **soap.client.class** : override client factory class
* **soap.server.class** : override server factory class
* **soap.dotNet** : enable dotNet mode, use of Soap\Client\DotNet class
* **soap.client.dotNet.class** : override client factory class in dotNet mode
* **soap.version** : define SOAP version, accept constant SOAP_1_1 or SOAP_1_2 as value (default is SOAP_1_2 unless in dotNet mode)

All parameters can be define at the instance level :

```php
$app['soap.instances'] = array(
    'connection_two' => array(
        'wsdl' => '<wsdl>anotherOne</wsdl>',
        'client.class' => '\stdClass',
        'server.class' => '\stdClass',
        'dotNet' => true,
        'client.dotNet.class' => '\stdClass',
        'version' => SOAP_1_1
    )
);
```
