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
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\LocaleServiceProvider;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Bus115\Timetable\Requester;
use Bus115\Timetable\Timetable;
use Bus115\Upload\Manager;
use Bus115\Upload\Lister;
use Bus115\Security\TokenAuthenticator;
use Bus115\Security\User\UserProvider;
use Bus115\Messenger\Messenger;
use Bus115\Messenger\Postback;
use Bus115\Messenger\Google;
use Bus115\Messenger\API;
use Bus115\Messenger\Response;
use Bus115\Messenger\TrimHelper;
use Bus115\Messenger\Messages\Image;
use Bus115\Messenger\Messages\FirstHandShake;
use Bus115\Messenger\Messages\Address;
use Bus115\Messenger\Messages\Joke;
use Bus115\Messenger\Messages\Help;
use Bus115\Messenger\Messages\AskLocation;
use Bus115\Messenger\Messages\Location;
use Bus115\Messenger\Messages\FallBack;
use Bus115\Messenger\Stops\Stops;
use Bus115\Messenger\Transports\Transports;

use Bus115\Telegram\SetWebhook;
use Bus115\Telegram\Webhook;
use Bus115\Telegram\Places\Places as TelegramPlaces;
use Bus115\Telegram\Stops\Stops as TelegramStops;
use Bus115\Telegram\Transports\Transports as TelegramTransports;
use Bus115\Telegram\Response as TelegramResponse;
use Bus115\Eway\Eway;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new DoctrineServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new LocaleServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));

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
        'page_access_token' => 'page_access_token',
        'telegram_token'    => 'telegram_token'
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
            'security'      => false,//(ENV == 'development') ? false : true,
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

$app['app.postback'] = function ($app) {
    return new Postback($app);
};

$app['app.google'] = function ($app) {
    return new Google($app);
};

$app['app.timetable'] = function ($app) {
    return new Timetable($app);
};

$app['app.requester'] = function ($app) {
    return new Requester($app);
};

$app['app.telegram.response'] = function ($app) {
    return new TelegramResponse($app);
};

$app['app.telegram.setwebhook'] = function ($app) {
    return new SetWebhook($app);
};

$app['app.telegram.places'] = function ($app) {
    return new TelegramPlaces($app);
};

$app['app.telegram.stops'] = function ($app) {
    return new TelegramStops($app);
};

$app['app.telegram.transports'] = function ($app) {
    return new TelegramTransports($app);
};

$app['app.telegram.webhook'] = function ($app) {
    return new Webhook($app);
};

$app['app.api'] = function ($app) {
    return new API($app);
};

$app['app.first_hand_shake'] = function ($app) {
    return new FirstHandShake($app);
};

$app['app.address'] = function ($app) {
    return new Address($app);
};

$app['app.joke'] = function ($app) {
    return new Joke($app);
};

$app['app.help'] = function ($app) {
    return new Help($app);
};

$app['app.fallback'] = function ($app) {
    return new FallBack($app);
};

$app['app.image'] = function ($app) {
    return new Image($app);
};

$app['app.ask_location'] = function ($app) {
    return new AskLocation($app);
};

$app['app.location'] = function ($app) {
    return new Location($app);
};

$app['app.trim_helper'] = function ($app) {
    return new TrimHelper();
};

$app['app.stops'] = function ($app) {
    return new Stops($app);
};

$app['app.transports'] = function ($app) {
    return new Transports($app);
};

$app['app.messenger_response'] = function ($app) {
    return new Response();
};

$app['app.eway'] = function ($app) {
    return new Eway($app);
};

$app['app.upload_manager'] = function ($app) {
    return new Manager($app);
};

$app['app.upload_lister'] = function ($app) {
    return new Lister($app);
};

return $app;
