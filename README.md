zend-soap-service-provider
==========================

A soap service provider for Silex, based on the ZendSoap component from ZendFramework project.

##Install

* add `"ibsciss/zend-soap-service-provider": "dev-master"` to your `composer.json` file. Next run the `composer install` command.
* register service : `$app->register(new ZendSoapServiceProvider();`;

##Usages

##Single instance

###Setup

At anytime, you can pass two options : 'soap.wsdl` and 'soap.config`.

```php
$app->register(new ZendSoapServiceProvider(), array(
    'soap.wsdl' => '<wsdl></wsdl>'; //your wsdl
));

//or 

$app->register(new ZendSoapServiceProvider());
$app['soap.wsdl'] = '<wsdl></wsdl>'; //your wsdl

```

###Defined services

You have access to two services :

* `soap.server` (alias of `zend_soap.server`) : return a shared instance of \Zend\Soap\Server
* `soap.client` (alias of `zend_soap.client`) : return a shared instance of \Zend\Soap\Client

```php
$app['soap.server']->getWsdl(); //for example
```

##Multiple instances

This provider is multiple instance ready, you can pass an array of configuration to create differente soap instances

```php
$app->register(new ZendSoapServiceProvider(), array(
    'soap.instances' => array('instance_one', 'instance_two')
));
```

You can access to your instance with the `soap.clients` or `soap.servers` keys like this : `$app['soap.clients']['instance_one']`.