<?php

//Set the models cache service
$di->set('modelsCache', function() {

    //Cache data for one day by default
    $frontCache = new Phalcon\Cache\Frontend\Data(array(
        "lifetime" => 86400
    ));

    //Memcached connection settings
    /*
    $cache = new Phalcon\Cache\Backend\Memcache($frontCache, array(
        "host" => "localhost",
        "port" => "11211"
    ));
    */

    $cache = new \Phalcon\Cache\Backend\Libmemcached($frontCache, array(
        "servers" => array(
            array('host' => 'localhost',
                    'port' => 11211,
                    'weight' => 1),
        ),
        "client" => array(
            Memcached::OPT_HASH => Memcached::HASH_MD5,
            Memcached::OPT_PREFIX_KEY => 'prefix.',
        )
    ));    

    return $cache;
});