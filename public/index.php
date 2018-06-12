<?php

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';

require __DIR__.'/../config/prod.php';

require __DIR__.'/../src/controllers.php';

$app->run();



//require_once __DIR__.'/../vendor/autoload.php';
//
//$app = new Silex\Application();
//
//// Doctrine
//$app->register(new Silex\Provider\DoctrineServiceProvider());
//
////debug
//$app['debug'] = true;
//
//$app->get('/hello/{name}', function ($name) use ($app) {
//    return 'Hello '.$app->escape($name);
//});
//
//$app->run();
