<?php

use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Loader;
use Phalcon\DI\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Postgresql as DbAdapter;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Mvc\Application;

define('ROOT_DIR',   realpath('..') . '/');
define('APP_DIR',    ROOT_DIR . 'app/');
define('PUBLIC_DIR', ROOT_DIR . 'public/');

try {

    // Read the configuration file
    $config = new ConfigIni(APP_DIR . 'config/config.ini');

    // Register an autoloader
    $loader = new Loader();
    $loader->registerDirs(array(
        ROOT_DIR . $config->application->controllersDir,
        ROOT_DIR . $config->application->modelsDir,
    ))->register();


    // Create a DI service container
    $di = new FactoryDefault();

    // Initialize session
    $di->set('session', function() {
        $session = new Session();
        $session->start();
        return $session;
    });

    // Setup database connection
    $di->set('db', function() use($config) {
        return new DbAdapter(array(
            'host' => $config->database->host,
            'username' => $config->database->username,
            'password' => $config->database->password,
            'dbname' => $config->database->dbname,
        ));
    });

    // Setup the view component (Volt engine)
    $di->set('volt', function($view, $di) use($config) {
        $volt = new Volt($view, $di);
        $volt->setOptions([
            'compiledPath' => ROOT_DIR . $config->application->voltCompileDir,
        ]);
        return $volt;
    });
    $di->set('view', function() use($config) {
        $view = new View();
        $view->setViewsDir(ROOT_DIR . $config->application->viewsDir);
        $view->registerEngines(['.volt' => 'volt']);
        return $view;
    });

    // Setup url provider
    $di->set('url', function() use($config) {
        $url = new UrlProvider();
        $url->setBaseUri($config->application->baseUrl);
        return $url;
    });

    // Setup router
    $di->set('router', function() {
        require APP_DIR . '/config/routes.php';
        return $router;
    });

    // Handle the request
    $app = new Application($di);
    $response = $app->handle();

    // Send the response
    echo $response->getContent();

} catch (Phalcon\Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage();
}

