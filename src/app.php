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

use Bus115\Security\TokenAuthenticator;
use Bus115\Security\User\UserProvider;

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
        'login' => 'login',
        'pass'  => 'pass',
        'city'  => 'city',
        'url'   => 'url',
        'token' => 'token'
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
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});

$app['app.token_authenticator'] = function ($app) {
    return new TokenAuthenticator();
};

return $app;
