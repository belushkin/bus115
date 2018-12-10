<?php

namespace Bus115\Telegram\Information;

use Silex\Application;
use Longman\TelegramBot\Request;

class ArrivalMessage
{

    private $app;
    private $message;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function text($stopId, $routeId)
    {
        $body       = $this->app['app.eway']->handleStopInfo($stopId);
        if (isset($body->routes) && is_array($body->routes)) {
            foreach ($body->routes as $route) {
                if ($route->id == $routeId) {
                    $string = $route->transportName . ' №' . $route->title . ', ';
                    $string .= 'в напрямку: ' . $route->directionTitle . ', ';
                    $string .= "прибуде через " . $route->timeLeftFormatted;

                    $data = [
                        'chat_id' => $this->getMessage()->getChat()->getId(),
                        'text'    => $string,
                    ];
                    return Request::sendMessage($data);
                }
            }
        }
        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => 'Час прибуття невідомий, оновіть своє місцезнаходження.',
        ];
        return Request::sendMessage($data);
    }
}
