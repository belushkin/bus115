<?php

namespace Bus115\Messenger\Transports;

use Silex\Application;

class Transports implements IdInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($id)
    {
        $routes     = $this->callStopInfo($id);
        $elements   = [];
        $responses  = [];
        $cache      = [];

        $i = 0;
        foreach ($routes as $route) {
            if (in_array($route->id, $cache)) { // removing duplicates from Eway API
                continue;
            }
            $elements[] = [
                'title'     => $route->transportName . ' №' . $route->title,
                'subtitle'  => 'В напрямку:' . $route->directionTitle,
                'image_url' => "https://bus115.kiev.ua/images/{$route->transportKey}.jpg",
                'buttons' => [
                    [
                        'type' => 'postback',
                        'title' => 'Оновити час прибуття',
                        'payload' => $id . '_'. $route->id
                    ]
                ]
            ];
            $i++;
            if ($i % 10 == 0) {
                $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
                $elements = [];
            }
            $cache[] = $route->id;
        }
        $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
        return $responses;
    }

    private function callStopInfo($id)
    {
        $body = $this->app['app.eway']->handleStopInfo($id);
        if (isset($body->routes) && is_array($body->routes) && !empty($body->routes)) {
            return $body->routes;
        }
        return [];
    }

}
