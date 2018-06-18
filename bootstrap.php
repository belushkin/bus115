<?php

require 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__.'/src/Entities'];
$isDevMode = isset($app['debug']) ? $app['debug'] : true;

$dbParams = include 'migrations-db.php';

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);
