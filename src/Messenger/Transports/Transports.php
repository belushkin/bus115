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

    public function text($payload)
    {
        if (strpos($payload, '_')) {
            $params = explode('_', $payload);
            $id     = intval($params[0]);
            $type   = $params[1];
        } else {
            $id     = intval($payload);
            $type   = false;
        }

        $this->app['monolog']->info("ROUTE: id" . $id . "type " . $type);

        $body     = $this->callStopInfo($id);
        $responses  = [];
        $cache      = [];

        $string = $body->title . "\n";
        foreach ($body->routes as $route) {
            if (in_array($route->id, $cache)) { // removing duplicates from Eway API
                continue;
            }
            if ($type == $route->transportKey) {
                continue;
            }
            $this->app['monolog']->info("ROUTE: TYPE" . $type . "TRANSPORT KEY " . $route->transportKey);

            $string .= $route->transportName. " №" . $route->title . ", ";
            $string .= "прибуде через " . $route->timeLeftFormatted;
            $string .= "\n";

            $cache[] = $route->id;
        }
        $responses[] = [
            'text' => $string,
            'quick_replies' => [
                [
                    'content_type'  => 'text',
                    'title'         => 'Оновити',
                    'payload'       => ($type) ? $id . '_' . $type : $id
                ]
            ]
        ];
        return $responses;
    }

    private function callStopInfo($id)
    {
        $body = $this->app['app.eway']->handleStopInfo($id);
        if (isset($body->routes) && is_array($body->routes) && !empty($body->routes)) {
            return $body;
        }
        return [];
    }

}
