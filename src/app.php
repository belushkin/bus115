<?php

namespace Bus115;

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Bus115\Security\TokenAuthenticator;
use Bus115\Security\User\UserProvider;
use Bus115\Messenger\Messenger;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new ValidatorServiceProvider());

$app['em'] = $entityManager;

$eway = ROOT_FOLDER . "/config/eway.php";
if (file_exists($eway)) {
    $app['eway'] = include ROOT_FOLDER . "/config/eway.php";
} else {
    $app['eway'] = [
        'login'             => 'login',
        'pass'              => 'pass',
        'city'              => 'city',
        'url'               => 'url',
        'token'             => 'token',
        'page_access_token' => 'page_access_token'
    ];
}

$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'webhook' => array(
            'pattern'       => 'webhook',
            'anonymous'      => true,
        ),
        'api' => array(
            'pattern'       => '^/api',
            'security'      => (ENV == 'development') ? false : true,
            'logout'        => true,
            'guard'         => array(
                'authenticators'  => array(
                    'app.token_authenticator'
                ),
            ),
            'users' => new UserProvider($entityManager)
        ),
        'main'       => array(
            'pattern'        => '^/',
            'anonymous'      => true,
        ),
    ),
));

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../data/cache/twig');
if (ENV == 'development') {
    $app['debug'] = true;
    $app->register(new WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => __DIR__.'/../data/cache/profiler',
    ));
} else {
    $app['debug'] = false;
}

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../data/logs/app.log',
));
$app->extend('monolog', function ($monolog, $app) {
    $monolog->pushHandler(new StreamHandler(__DIR__.'/../data/logs/info.log', Logger::INFO));
    return $monolog;
});
$app->extend('monolog', function ($monolog, $app) {
    $monolog->pushHandler(new StreamHandler(__DIR__.'/../data/logs/error.log', Logger::ERROR));
    return $monolog;
});


$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});

$app['app.token_authenticator'] = function ($app) {
    return new TokenAuthenticator();
};

$app['app.messenger'] = function ($app) {
    return new Messenger($app);
};

return $app;
