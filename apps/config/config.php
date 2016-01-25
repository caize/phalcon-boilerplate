<?php
/**
 * Main settings
 */

return new \Phalcon\Config(array(
    'database' => array(
        'adapter'  => 'Mysql',
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'secret',
        'name'     => 'some-db-name',
    ),
    'application' => array(
        'aws' => array(
            'services' => array(
                's3' => array(
                    'buckets' => array(
                            'repo-key' => array(
                                            'name' => 'awsrepo',
                                            'aws_endpoint' => 'awsrepo.s3-us-west-2.amazonaws.com',
                                            'public_endpoint' => 'awsrepo.s3-us-west-2.amazonaws.com'),
                            //you can add more repos here if you need to
                        )
                    ))
        ),

        //urls
        'url' => array(
            //I find it handy to setup your module's URLs somewhere (if it applies)
            'business' => 'http://business.sample.com/',
            'api' => 'http://api.sample.com/',
        ),

        //main
        'models_dir' => APPS_DIR . '/models/',
        'library_dir' => APPS_DIR . '/lib/',
        'cache_dir' => APPS_DIR . '/cache/',

        //security: hashes and keys
        'security' => array(
            'tokens' => array('usage-type-1' => array('alive' => 30, //time in mins
                                            'salt' => 'Some@Salt',
                                            'method' => 'hmac',
                                            'algo' => 'sha256'),
                            'usage-type-2' => array('alive' => -1, //unlimited
                                            'salt' => '||Some@Salt||',
                                            'method' => 'hmac',
                                            'algo' => 'sha256'),
                            ),
                            //add more token types if you need to
            'aws' => array(
                //these are root credentials
                'key' => 'your-key',
                'secret' => 'your-key-secret'),
        ),

        //langs :: http://www.w3schools.com/tags/ref_language_codes.asp
        'lang' => array(
            'default' => 'es',
            'supported' => array(
                'en', 'es', 'pt', 'fr', 'it', 'de')
        ),

        //datetime
        'dt_format' => array(
            'default' => 'm/d/y h:i a',
            'supported' => array(
                'd/m/Y h:i a', 'd/m/y h:i a', 'd/m/Y H:i', 'd/m/y H:i',
                'm/d/Y h:i a', 'm/d/y h:i a', 'm/d/Y H:i', 'm/d/y H:i',
                'Y/m/d h:i a', 'y/m/d h:i a', 'Y/m/d H:i', 'y/m/d H:i')
        ),

        //mailer
        'mailer' => array(
            'default' => 'mandrill',
            'mandrill' => array(
                'account' => 'your-account',
                'description' => 'your-app-name',
                'key' => 'valid-key',
                'password' => null),
            'sendgrid' => array(
                'account' => 'your-account',
                'description' => 'your-app-name',
                'key' => null,
                'password' => 'some-password')
        ),
    ),
));