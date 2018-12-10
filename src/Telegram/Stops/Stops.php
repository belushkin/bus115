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
            $subtitles  = [];
            foreach ($body->stop as $stop) {
                $button = new InlineKeyboardButton(['text' => 'Обрати', 'callback_data' => 'stop_' . $stop->id]);
                $keyboard = new InlineKeyboard($button);
                $keyboard->setResizeKeyboard(true);

                $subtitle = $this->app['app.stops']->getStopSubtitle($stop->routes);
                if (in_array($subtitle, $subtitles)) {
                    continue;
                }
                $elements[] = [
                    'chat_id'       =>  intval($this->getMessage()->getChat()->getId()),
                    'latitude'      =>  $stop->lat,
                    'longitude'     =>  $stop->lng,
                    'title'         =>  $stop->title,
                    'address'       =>  'Транспорт: ' . $subtitle,
                    'reply_markup'  =>  $keyboard
                ];
                $subtitles[] = $subtitle;
            }
            return $this->app['app.telegram.response']->venues($elements);
        }

        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
        ];
        $result = Request::sendMessage($data);
        if (!$result->isOk()) {
            $this->app['monolog']->info("Stops ERROR " . $result->getDescription());
            return Request::emptyResponse();
        }
        return $result;
    }

}
