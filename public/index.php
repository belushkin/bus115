<?php

require __DIR__. '/../config/constants.php';

if (file_exists(ROOT_FOLDER . '/config/dev.php')) {
    define("ENV", "development");
    ini_set('display_errors', 1);
} else {
    define("ENV", "production");
    ini_set('display_errors', 0);
}

require_once __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../bootstrap.php';
$app = require __DIR__.'/../src/app.php';

if (ENV == 'development') {
    require ROOT_FOLDER . '/config/dev.php';
} else {
    require ROOT_FOLDER.'/config/prod.php';
}
require __DIR__.'/../src/controllers.php';

$app->run();
