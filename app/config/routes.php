<?php

use Phalcon\Mvc\Router;

$router = new Router();

$router->add('/login', array(
    'controller' => 'users',
    'action' => 'login',
));

return $router;

