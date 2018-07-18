<?php

namespace Bus115;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Bus115\Entities\Product;

/**
 * @api {Get} /site/feature/config/:uuid/delete
 * delete feature config
 * @apiName Bus115
 * @apiGroup Site
 * @apiVersion 1.0.0
 *
 * @apiParam {String} uuid
 *
 */
$app->get('/', function () use ($app, $entityManager) {

//    $product = new Product();
//    $product->setName('ssss');
//
//    $entityManager->persist($product);
//    $entityManager->flush();
//
//    echo "Created Product with ID " . $product->getId() . "\n";

    return $app['twig']->render('index.html.twig', array());
})
    ->bind('homepage')
;

$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
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
