zend-soap-service-provider
==========================

A soap service provider for Silex, based on the ZendSoap component from ZendFramework project.

For more informations about Zend Soap, check the Zend Framework documentation : 
* [Zend Soap Server](http://framework.zend.com/manual/2.2/en/modules/zend.soap.server.html)
* [Zend Soap Client](http://framework.zend.com/manual/2.2/en/modules/zend.soap.client.html)

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
* `soap.server` : a `Zend\Soap\Server` instance
* `soap.client` : a `Zend\Soap\Client` instance

```php
//client method call
$app['soap.client']->methodCall();

//server handling
$app['soap.server']->handle();
```

###Multiple instances

If you need more than only one connexion, you can define differents instances using `soap.instances` parameters.

```php
//during registration
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connexion_one', 
        'connexion_two'
    );
));

// --- OR --- 
$app->register(new ZendSoapServiceProvider());
$app['soap.instances'] = array(
    'connexion_one', 
    'connexion_two'
);
```

You have access to you instances with the both `soap.clients` and `soap.servers` services.
The first defined service became the default one and is also accessible from `soap.client` and `soap.server` services.

```php
$app['soap.clients']['connexion_one']; //equivalent to $app['soap.client'];
$app['soap.servers']['connexion_two'];
```

###WSDL management

You can provide a (optional) WSDL for the global service with the `soap.wsdl` parameters.

```php
//during registration
$app->register(new ZendSoapServiceProvider(), array(
    'soap.wsdl' => '<wsdl></wsdl>';
));
// --- OR --- 
$app->register(new ZendSoapServiceProvider());
$app['soap.wsdl'] = '<wsdl></wsdl>';

$app['soap.server]->getWsdl(); //return <wsdl></wsdl>
```

For multiple instances, maybe you want to define another wsdl for a specific instance :

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
$app->register(new ZendSoapServiceProvider());
$app['soap.wsdl'] = '<wsdl></wsdl>';
$app['soap.instances'] = array(
    'connexion_one'
    'connexion_two' => array('wsdl' => '<wsdl>anotherOne</wsdl>')
);

//you don't have to specify global wsdl if you provide one for each instance
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

If you want to use your own personal soap class, or for test purpose, you can override the soap server or client class.
* At the global level 
* At an instance level

```php
//global level
$app = new Application();
$app->register(new ZendSoapServiceProvider());

$app['soap.server.class'] = '\stdClass';
$app['soap.client.class'] = '\stdClass';

$app['soap.client']; //instanceOf stdClass;
$app['soap.server']; //instanceOf stdClass;

//----------------
//example for a specific instance override level
$app = new Application();
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connexion_one' => array('soap.server.class' => '\stdClass'),
        'connexion_two' => array('soap.client.class' => '\stdClass')
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

//you can also define it at the instance level
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array(
        'connexion_one' => array('dotNet' => true),
        'connexion_two'
    )
));

$app['soap.clients']['connexion_one']; //instanceOf Zend\Soap\Client\DotNet
$app['soap.clients']['connexion_two']; //instanceOf Zend\Soap\Client
```

**If you override the soap.client.class the dotNet option is disabled an the provide class is used instead.**