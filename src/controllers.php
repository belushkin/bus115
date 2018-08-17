<?php

namespace Bus115;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\ParameterBag;
use Bus115\Upload\Manager;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
});

$app->get('/', function (Request $request) use ($app) {
    $verifyToken    = $request->get('verifyToken');
    return $app['twig']->render('index.html.twig', array(
        'verifyToken'   => $verifyToken,
        'token'         => $app['eway']['token']
    ));
})->bind('homepage');

$app->get('/upload_stop', function (Request $request) use ($app) {
    $verifyToken    = $request->get('verifyToken');
    return $app['twig']->render('upload_stop.html.twig', array(
        'verifyToken'   => $verifyToken,
        'token'         => $app['eway']['token']
    ));
});

$app->get('/upload_transport', function (Request $request) use ($app) {
    $verifyToken    = $request->get('verifyToken');
    return $app['twig']->render('upload_transport.html.twig', array(
        'verifyToken'   => $verifyToken,
        'token'         => $app['eway']['token']
    ));
});

$app->post('/upload_stop', function (Request $request) use ($app) {
    $file           = $request->files->get('file');
    $description    = $request->request->get('arr');
    $uploadmanager  = $app['app.upload_manager'];

    if (!empty($file)) {
        $uploadmanager->manage($file, $description, 'stop');
        return new Response();
    } else {
        return new Response("An error ocurred. Did you really send a file?");
    }
});

$app->post('/upload_transport', function (Request $request) use ($app) {
    $file           = $request->files->get('file');
    $description    = $request->request->get('arr');
    $uploadmanager  = $app['app.upload_manager'];

    if (!empty($file)) {
        $uploadmanager->manage($file, $description, 'transport');
        return new Response();
    } else {
        return new Response("An error ocurred. Did you really send a file?");
    }
});

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

    $response = $app['app.eway']->getStopsNearPoint($lat, $lng);
    return new Response(\GuzzleHttp\json_encode($response));
});

/**
 * @api {Post} /api/v1/converter
 * Move uploaded image to Stop or to Transport
 * @apiName Move uploaded image
 * @apiDescription Move uploaded image to Stop or to Transport
 *
 * @apiGroup Tools
 * @apiVersion 1.0.0
 * @apiSampleRequest converter
 *
 * @apiParam {Number} image_uuid Mandatory Uploaded image uuid
 * @apiParam {Number} eway_id Mandatory Eway Id
 * @apiParam {String} type Mandatory Stop or Transport
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
 *     curl -H "X-AUTH-TOKEN: 23234defdewfewf" -i http://0.0.0.0:8080/api/v1/converter?image_id=12&eway_id=333&type=stop
 *
 * @apiSuccess {Array[]}  entity                Array of entities.
 * @apiSuccess {Number}   entity.uuid           Entity uuid.
 * @apiSuccess {Number}   entity.description    Entity description.
 *
 */
$app->post('/api/v1/converter', function (Request $request) use ($app) {
    $uuid           = $request->request->get('imageUuid');
    $name           = $request->request->get('imageName');
    $ewayId         = intval($request->request->get('ewayId'));
    $type           = $request->request->get('type');
    $verifyToken    = $request->request->get('verifyToken');
    $transportType  = $request->request->get('transportType');

    $errors = $app['validator']->validate($uuid, new Assert\Uuid());
    if (count($errors) > 0) {
        $app->abort(403, "Invalid Image uuid");
        return false;
    }

    $uploadManager  = $app['app.upload_manager'];
    if ($verifyToken == $app['eway']['token'] && in_array($type, [Manager::TYPE_STOP, Manager::TYPE_TRANSPORT])) {
        if ($uploadManager->move($type, $uuid, $ewayId, $name, $transportType)) {
            return new Response('EVENT_RECEIVED');
        }
        return new Response('EVENT_REJECTED');
    }
    $app->abort(403, "Invalid Verify Token or Type");
});

$app->get('/uploaded_images', function (Request $request) use ($app) {
    $verifyToken    = $request->get('verifyToken');
    $type           = $request->get('type');

    if ($verifyToken == $app['eway']['token'] && in_array($type, [Manager::TYPE_STOP, Manager::TYPE_TRANSPORT])) {
        $folder = ($type == Manager::TYPE_STOP) ? Manager::FOLDER_STOPS : Manager::FOLDER_TRANSPORTS;
        return $app['twig']->render('uploaded_images.html.twig', array(
            'images' => $app['app.upload_lister']->manage(
                __DIR__.'/../public/upload/'.$folder.'/',
                $type
            ),
            'verify_token' => $verifyToken,
            'type' => $type,
        ));
    }
    $app->abort(403, "Invalid Verify Token or Type");
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
            $app['monolog']->info(sprintf('Handle Message'));
            $app['app.messenger']->handle($senderPsid, $webhookEvent['message'], $webhookEvent['message']['nlp']);
        } else if ($webhookEvent['postback']) {
            $app['monolog']->info(sprintf('Handle Postback'));
            $app['app.postback']->handle($senderPsid, $webhookEvent['postback'], []);
        }

        return new Response('EVENT_RECEIVED');
    }
    $app->abort(404, "Not Found");
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $app['monolog']->error(sprintf('Exception %s, %s', $e->getCode(), $e->getMessage()));

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
