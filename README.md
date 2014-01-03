zend-soap-service-provider
==========================

A soap service provider for Silex, based on the ZendSoap component from ZendFramework project.

For more informations about Zend Soap, check the Zend Framework documentation : 
* [Zend Soap Server](http://framework.zend.com/manual/2.2/en/modules/zend.soap.server.html)
* [Zend Soap Client](http://framework.zend.com/manual/2.2/en/modules/zend.soap.client.html)

The whole projet is unit tested : 

##Why a Zend Soap silex service provider ?

* For testing
* For a better integration
* For simplicity

##Install

1. Add `"ibsciss/zend-soap-service-provider": "dev-master"` in the require section of your `composer.json` and run the `composer install` command.
2. Register service : `$app->register(new ZendSoapServiceProvider());`;

##Usages

###Basic usages

When the service provider is registred, you have access to the two basic services :
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

If you need more connexion, you can define several instances using `soap.instances` parameters.

```php
//during registration
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connexion_one', 
        'connexion_two'
    )
));

// --- OR --- 
$app->register(new ZendSoapServiceProvider());
$app['soap.instances'] = array(
    'connexion_one', 
    'connexion_two'
);
```

You have access to you instances with the two services :
* `soap.clients` 
* `soap.servers`

*The first defined service is the default one and became accessible from `soap.client` and `soap.server` services.*

```php
$app['soap.clients']['connexion_one']; //same as $app['soap.client'];
$app['soap.servers']['connexion_two'];
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
        'connexion_one', 
        'connexion_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
    );
));

// --- OR --- 
$app['soap.wsdl'] = '<wsdl></wsdl>';
$app['soap.instances'] = array(
    'connexion_one'
    'connexion_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
);

//if you provide one wsdl per instance you don't have to specify a global one 
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connexion_one' => array('wsdl' => '<wsdl></wsdl>'), 
        'connexion_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
    );
));

$app['soap.servers']['connexion_one']->getWsdl() //return <wsdl></wsdl>
$app['soap.servers']['connexion_two']->getWsdl() //return <wsdl>anotherOne</wsdl>
```

##Advanced topic 

###Change Soap class

If you want to use your own personal soap class, or for test purpose. You can override the soap, server or client, class.

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
        'connexion_one' => array('server.class' => '\stdClass'),
        'connexion_two' => array('client.class' => '\stdClass')
    )
));

$app['soap.clients']['connexion_one']; //instanceOf Zend\Soap\Client
$app['soap.servers']['connexion_one']; //instanceOf stdClass

$app['soap.clients']['connexion_two']; //instanceOf stdClass
$app['soap.servers']['connexion_two']; //instanceOf Zend\Soap\Server
```

###DotNet specific mode

The dotNet framework process soap parameters a little differente than PHP or Java implementations. 

So, if you have to integrate your soap webservices with a dotNet server, set the `soap.dotNet` option at `true`.

```php
$app['soap.dotNet'] = true;
$app['soap.client'] // instanceOf Zend\Soap\Client\DotNet

//you can also define it at the instance scope
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connexion_one' => array('dotNet' => true),
        'connexion_two'
    )
));

$app['soap.clients']['connexion_one']; //instanceOf Zend\Soap\Client\DotNet
$app['soap.clients']['connexion_two']; //instanceOf Zend\Soap\Client
```

*If you override the `soap.client.class` the dotNet option is disabled and the provided class is used instead.*

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

All parameters can be define at the instance level :

```php
$app['soap.instances'] = array(
    'connexion_two' => array(
        'wsdl' => '<wsdl>anotherOne</wsdl>',
        'client.class' => '\stdClass',
        'server.class' => '\stdClass',
        'dotNet' => true
    )
);
```
