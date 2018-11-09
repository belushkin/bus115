<?php

namespace Bus115\Telegram\Stops;

use Silex\Application;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class Stops
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

    public function text($lat, $lng)
    {
        $body       = $this->app['app.eway']->getStopsNearPoint($lat, $lng);
        if (isset($body->stop) && is_array($body->stop) && !empty($body->stop)) {
            $elements   = [];
            foreach ($body->stop as $stop) {
                $button = new InlineKeyboardButton(['text' => 'select', 'callback_data' => 'stop_' . $stop->id]);
                $keyboard = new InlineKeyboard($button);
                $keyboard->setResizeKeyboard(true);

                $elements[] = [
                    'chat_id'       =>  $this->getMessage()->getChat()->getId(),
                    'latitude'      =>  $stop->lat,
                    'longitude'     =>  $stop->lng,
                    'title'         =>  $stop->title,
                    'address'       =>  'В напрямку ' . $this->app['app.stops']->getStopDirection($stop->id),
                    'reply_markup'  =>  $keyboard
                ];
            }
            return $this->app['app.telegram.response']->venues($elements);
        }

        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => 'Нічого не знайдено, для допомоги надрукуй help',
        ];
        return Request::sendMessage($data);
    }

}
