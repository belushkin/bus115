<?php

require __DIR__. '/../config/constants.php';
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
