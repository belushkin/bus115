<?php

namespace Bus115\Telegram\Transports;

use Silex\Application;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class Transports
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

    public function text($id)
    {
        $routes     = $this->callStopInfo($id);

        $cache      = [];
        $text       = [];

        $text[] = '*Транспорт:*';
        $text[] = '';
        foreach ($routes as $route) {
            if (in_array($route->id, $cache)) { // removing duplicates from Eway API
                continue;
            }

            $string = '*'.$route->transportName.'*' . ' №' . $route->title . ', ';
            //$string .= 'в напрямку: ' . $route->directionTitle . ', ';
            $string .= 'прибуде через ' . $route->timeLeftFormatted;

            $text[] = $string;
            //$text[] = '';
            $cache[] = $route->id;
        }

        $button = new InlineKeyboardButton(['text' => 'Оновити', 'callback_data' => $id]);
        $keyboard = new InlineKeyboard($button);

        $keyboard->setResizeKeyboard(true);

        $data['chat_id']        = $this->getMessage()->getChat()->getId();
        $data['text']           = implode(PHP_EOL, $text);
        $data['parse_mode']     = 'Markdown';
        $data['reply_markup']   = $keyboard;

        return Request::sendMessage($data);
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
