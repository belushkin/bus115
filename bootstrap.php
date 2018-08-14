<?php

require 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__.'/src/Entity'];
$isDevMode = isset($app['debug']) ? $app['debug'] : true;

if (file_exists('migrations-db.php')) {
    $dbParams = include 'migrations-db.php';
} else {
    $dbParams = [
        'dbname' => 'dbname',
        'user' => 'user',
        'password' => 'password',
        'host' => 'host',
        'driver' => 'pdo_mysql',
    ];
}

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);
