<?php

$basedomain = $_SERVER['SERVER_NAME'];
//apply rules if you need to tweak your basedomain


use Phalcon\Mvc\Router;


$router = new Router();
$router->removeExtraSlashes(true);

/*
 * Business routes
 */

$router->add("/", array(
    'module'     => 'business',
    'controller' => 'index',
    'action'     => 'index',
))->setHostName('business.'.$basedomain);

$router->add("/:controller", array(
    'module'     => 'business',
    'controller' => 1,
    'action'     => 'index',
))->setHostName('business.'.$basedomain);

$router->add("/:controller/:action", array(
    'module'     => 'business',
    'controller' => 1,
    'action'     => 2,
))->setHostName('business.'.$basedomain);
$router->add("/:controller/:action/:params", array(
    'module'     => 'business',
    'controller' => 1,
    'action'     => 2,
    'params'     => 3,
))->setHostName('business.'.$basedomain);

/*
 * API routes
 */

$router->add("/", array(
    'module'     => 'api',
    'controller' => 'index',
    'action'     => 'index',
))->setHostName('api.'.$basedomain);

$router->add("/{version}", array(
    'module'     => 'api',
    'controller' => 'index',
    'action'     => 'index',
))->setHostName('api.'.$basedomain);

$router->add("/{version}/:controller", array(
    'module'     => 'api',
    'controller' => 2,
    'action'     => 'index',
))->setHostName('api.'.$basedomain);

$router->add("/{version}/:controller/:action", array(
    'module'     => 'api',
    'controller' => 2,
    'action'     => 3,
))->setHostName('api.'.$basedomain);

$router->add("/{version}/:controller/:action/{params}", array(
    'module'     => 'api',
    'controller' => 2,
    'action'     => 3,
))->setHostName('api.'.$basedomain);

return $router;