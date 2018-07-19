<?php

namespace Bus115;

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\SecurityServiceProvider;

use Bus115\Security\TokenAuthenticator;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'main'       => array(
            'pattern'        => '^/',
            'anonymous'      => true,
            'logout'         => true,
            'guard'          => array(
                'authenticators'  => array(
                    'app.token_authenticator'
                ),
            ),
            // ...
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
