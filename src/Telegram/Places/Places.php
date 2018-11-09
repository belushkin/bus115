<?php

namespace Bus115\Telegram\Places;

use Silex\Application;
use Longman\TelegramBot\Commands\UserCommands\HelpCommand;
use Longman\TelegramBot\Commands\UserCommands\WhoamiCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Location;
use Longman\TelegramBot\Entities\Venue;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class Places
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

    public function text($term)
    {
        \Longman\TelegramBot\TelegramLog::debug(sprintf('TELEGRAM SEARCH WORKS, User entered Term: %s', $term));
        $term = $this->app['app.regular_text']->stripTerms($term);
        \Longman\TelegramBot\TelegramLog::debug(sprintf('Term after STRIP: %s', $term));

        if (empty($term) || strlen($term) < 4) {
            $data = [
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text'    => 'Введіть запит більше 4 символів',
            ];
            return Request::sendMessage($data);
        }

        return $this->searchPlaces($term);
    }

    private function searchPlaces($term)
    {
        $body = $this->app['app.eway']->getPlacesByName($term);

        \Longman\TelegramBot\TelegramLog::debug(sprintf('Started searchingg'));
        if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
            \Longman\TelegramBot\TelegramLog::debug(sprintf('Started iterating'));
            $elements  = [];
            foreach ($body->item as $item) {
                $button = new InlineKeyboardButton(['text' => 'select', 'callback_data' => 'stop_' . $item->id]);
                $keyboard = new InlineKeyboard($button);
                $keyboard->setResizeKeyboard(true);

                $elements[] = [
                    'chat_id'       =>  $this->getMessage()->getChat()->getId(),
                    'latitude'      =>  $item->lat,
                    'longitude'     =>  $item->lng,
                    'title'         =>  $item->title,
                    'address'       =>  'В напрямку ' . $this->app['app.regular_text']->getStopDirection($item->id),
                    'reply_markup'  =>  $keyboard
                ];
            }
            \Longman\TelegramBot\TelegramLog::debug(sprintf('Started returning'));
            return $this->app['app.telegram.response']->venues($elements);
        } else {
            try {
                \Longman\TelegramBot\TelegramLog::debug(sprintf('Telegram Google works'));
                $results = $this->app['app.api']->getGoogleCoordinates($term);
            } catch (\InvalidArgumentException $e) {
                $results = [];
            }
            return $this->getStopsByGoogleCoordinates($results);
        }
    }

    private function getStopsByGoogleCoordinates($results)
    {
        if (isset($results->results[0]->geometry->location)) {
            $location = $results->results[0]->geometry->location;
            \Longman\TelegramBot\TelegramLog::debug(sprintf('Google LOCATION, %s', \GuzzleHttp\json_encode($location)));

            return $this->app['app.telegram.stops']->
            setMessage($this->getMessage())->
            text($location->lat, $location->lng);
        }

        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => 'Нічого не знайдено, для допомоги надрукуй help',
        ];
        return Request::sendMessage($data);
    }

}
