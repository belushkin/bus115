<?php

namespace Bus115;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ParameterBag;
use GuzzleHttp\Client;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

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
 *     curl -H "X-AUTH-TOKEN: 23234defdewfewf" -i http://0.0.0.0:8080/api/v1/getstops?lat=4711&lng=4567
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

/**
 * @api {GET} /api/v1/webhook
 * Webhook verification
 * @apiName Webhook verification
 * @apiDescription To ensure your webhook is authentic and working
 *
 * @apiGroup Facebook
 * @apiVersion 1.0.0
 * @apiSampleRequest webhook
 *
 * @apiParam {String} hub_verify_token Mandatory Facebook verify token.
 * This is a random string of your choosing, hardcoded into your webhook.
 * @apiParam {String} hub_challenge Mandatory Facebook sends this parameter in request and it must be returned back
 * @apiParam {String} hub_mode Mandatory Facebook mode parameter
 *
 * @apiExample {curl} Example usage:
 *     curl -X GET "http://0.0.0.0:8080/api/v1/webhook?hub_verify_token=<YOUR_VERIFY_TOKEN>&hub_challenge=CHALLENGE_ACCEPTED&hub_mode=subscribe"
 *
 */
$app->get('/api/v1/webhook', function (Request $request) use ($app) {
    $verifyToken    = $request->get('hub_verify_token');
    $hubChallenge   = $request->get('hub_challenge');
    $mode           = $request->get('hub_mode');

    if ($verifyToken && $mode) {
        if ($verifyToken === $app['eway']['token'] && $mode == 'subscribe') {
            return new Response($hubChallenge);
        }
    }
    $app->abort(403, "Invalid Verify Token");
});

/**
 * @api {POST} /api/v1/webhook
 * Webhook endpoint
 * @apiName Webhook endpoint
 * @apiDescription Endpoint that accepts POST requests, checks the request is a webhook event, then parses the message.
 *
 * @apiGroup Facebook
 * @apiVersion 1.0.0
 * @apiSampleRequest webhook
 *
 * @apiExample {curl} Example usage:
 *     curl -H "Content-Type: application/json" -X POST "http://0.0.0.0:8080/api/v1/webhook" -d '{"object": "page", "entry": [{"messaging": [{"message": "TEST_MESSAGE"}]}]}'
 *
 */
$app->post('/api/v1/webhook', function (Request $request) use ($app) {
    $object     = $request->request->get('object');
    $entry      = $request->request->get('entry');
    if ($object == 'page') {
        $webhookEvent   = $entry[0]['messaging'][0];
        $senderPsid     = $webhookEvent['sender']['id'];
        $app['monolog']->info(sprintf('Sender Psid: %s', $senderPsid));

        // Check if the event is a message or postback and
        // pass the event to the appropriate handler function
        if (isset($webhookEvent['message'])) {
            $app['app.messenger']->handleMessage($senderPsid, $webhookEvent['message']);
        } else if ($webhookEvent['postback']) {
            $app['app.messenger']->handlePostback($senderPsid, $webhookEvent['postback']);
        }

        return new Response('EVENT_RECEIVED');
    }
    $app->abort(404, "Not Found");
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
