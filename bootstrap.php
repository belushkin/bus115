<?php

require 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__.'/src/Entity'];
$isDevMode = isset($app['debug']) ? $app['debug'] : true;

if (file_exists('migrations-db.php')) {
    $dbParams = include 'migrations-db.php';
} else if (file_exists(ROOT_FOLDER . '/migrations-db.php')) {
    $dbParams = include ROOT_FOLDER . '/migrations-db.php';
} else {
    $dbParams = [
        'dbname' => 'dbname',
        'user' => 'user',
        'password' => 'password',
        'host' => 'host',
        'driver' => 'pdo_mysql',
        'charset'  => 'utf8',
        'driverOptions' => array(
            1002 => 'SET NAMES utf8'
        )
    ];
}

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);

// **Fatal error**: Uncaught exception 'Doctrine\DBAL\DBALException' with message 'Unknown database type enum requested, Doctrine\DBAL\Platforms\MySqlPlatform may not support it.'
$platform = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');
$platform->registerDoctrineTypeMapping('set', 'string');
