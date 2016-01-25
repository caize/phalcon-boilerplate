<?php

namespace Promoziti\Modules\Api;

use Phalcon\Loader,
    Phalcon\Mvc\Dispatcher,
    Phalcon\Mvc\View,
    Phalcon\Mvc\View\Engine\Volt as VoltEngine,
    Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter,
    Phalcon\Mvc\ModuleDefinitionInterface,
    Phalcon\DiInterface;

class Module implements ModuleDefinitionInterface
{

    /**
     * Register a specific autoloader for the module
     */
    public function registerAutoloaders(DiInterface $dependencyInjector = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces( array(
            'Promoziti\Modules\Api\Controllers'        => __DIR__ .'/controllers/',
            'Promoziti\Models\Entities'                => __DIR__ .'/../../models/entities/',
            'Promoziti\Models\Services'                => __DIR__ .'/../../models/services/',
            'Promoziti\Models\Services\Service'        => __DIR__ .'/../../models/services/service/',
            'Promoziti\Models\Services\Exceptions'     => __DIR__ .'/../../models/services/exceptions/',
            'Promoziti\Models\Repositories'            => __DIR__ .'/../../models/repositories/',
            'Promoziti\Models\Repositories\Repository' => __DIR__ .'/../../models/repositories/repository/',
            'Promoziti\Models\Repositories\Exceptions' => __DIR__ .'/../../models/repositories/exceptions/',
            'Promoziti\Lib\Core'                       => __DIR__ .'/../../lib/core/',
            'Promoziti\Lib\Core\Mail'                  => __DIR__ .'/../../lib/core/mail/',
            'Promoziti\Lib\Core\Sms'                   => __DIR__ .'/../../lib/core/sms/',
            'Promoziti\Lib\Core\Crypt'                 => __DIR__ .'/../../lib/core/crypt/',
        ));
    
        include_once(__DIR__ .'/../../lib/vendors/aws-sdk-php-2.7.6/aws-autoloader.php');
        include_once(__DIR__ .'/../../lib/vendors/twilio-php/Services/Twilio.php');

        $loader->register();
    }

    /**
     * Register specific services for the module
     */
    public function registerServices(DiInterface $di)
    {
        $config = $di->get('config');

        //Registering a dispatcher
        $di->set('dispatcher', function() {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace("Promoziti\Modules\Api\Controllers");
            return $dispatcher;
        });

        //empty view because API only returns JSON
        $di->set('view', function() {
            $view = new View();
            return $view;
        });



        $di->set('aws_s3', function() use ($config) {
            
            //version 2.7 style
            $s3 = \Aws\S3\S3Client::factory(array(
                'key'    => $config->application->security->aws->key,
                'secret' => $config->application->security->aws->secret,
                'region' => 'us-west-2',
                'version' => '2006-03-01',
            ));
            
            return $s3;

        }); 
    }

}