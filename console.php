<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__ . '/config/constants.php';
require __DIR__ . '/bootstrap.php';
$app = require __DIR__.'/src/app.php';
require __DIR__ . '/config/dev.php';
require __DIR__.'/src/controllers.php';

list($_, $method, $path) = $argv;
$request = Request::create($path, $method);
$app->run($request);
