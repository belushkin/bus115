<?php

namespace Bus115;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use GuzzleHttp\Client;

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})->bind('homepage');

/**
 * @api {Get} /api/v1/getstops
 * Get Stops near Point
 * @apiName Get Stops near Point
 * @apiDescription Returns a list of stops in the vicinity of a given point
 *
 * @apiGroup Stops
 * @apiVersion 1.0.0
 * @apiSampleRequest getstops
 *
 * @apiParam {Number} lat Mandatory Latitude
 * @apiParam {Number} lng Mandatory Longitude
 *
 * @apiPermission admin
 *
 * @apiHeader {String} X-AUTH-TOKEN Users unique access-token.
 *
 * @apiHeaderExample {json} Header-Example:
 *     {
 *       "X-AUTH-TOKEN": "23234defdewfewf"
 *     }
 *
 * @apiExample {curl} Example usage:
 *     curl -H "X-AUTH-TOKEN: 23234defdewfewf" -i http://0.0.0.0/api/v1/getstops?lat=4711&lng=4567
 *
 * @apiSuccess {Array[]}  stop      Array of stops.
 * @apiSuccess {Number}   stop.lat   Stop Latitude.
 * @apiSuccess {Number}   stop.lng   Stop Longitude.
 * @apiSuccess {String}   stop.title Stop title.
 *
 */
$app->get('/api/v1/getstops', function (Request $request) use ($app) {

    $lat = $request->get('lat');
    $lng = $request->get('lng');

    $errors = $app['validator']->validate($lat, new Assert\Type('numeric'));
    if (count($errors) > 0) {
        $errorsString = (string) $errors;
        return new Response($errorsString);
    }
    $errors = $app['validator']->validate($lng, new Assert\Type('numeric'));
    if (count($errors) > 0) {
        $errorsString = (string) $errors;
        return new Response($errorsString);
    }

    $client = new Client();
    $response = $client->request('GET', $app['eway']['url'], [
        'query' => [
            'login'     => $app['eway']['login'],
            'password'  => $app['eway']['pass'],
            'function'  => 'stops.GetStopsNearPoint',
            'city'      => $app['eway']['city'],
            'lat'       => $lat,
            'lng'       => $lng,
        ]
    ]);

    $body = \GuzzleHttp\json_decode($response->getBody());
    return new Response(\GuzzleHttp\json_encode($body));
});


$app->get('/api/v1/webhook', function (Request $request) use ($app) {

    return new Response(\GuzzleHttp\json_encode($body));
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
