<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class ArrivalMessage implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    // Final output as a text to the messenger
    public function text($payload = '')
    {
        $params = explode('_', $payload);
        if (intval($params[0]) == 0) { // stop id
            return [];
        }

        $body       = $this->app['app.eway']->handleStopInfo($params[0]);
        if (isset($body->routes) && is_array($body->routes)) {
            foreach ($body->routes as $route) {
                if ($route->id == $params[1]) {
                    $string = $route->transportName . ' №' . $route->title . ', ';
                    $string .= 'в напрямку: ' . $route->directionTitle . ', ';
                    $string .= "буде через " . $route->timeLeftFormatted;
                    $responses[] = [
                        'text' => $string
                    ];
                    return $responses;
                }
            }
        }
        $responses[] = [
            'text' => "Час прибуття невідомий, оновіть своє місцезнаходження.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        return $responses;
    }

}
