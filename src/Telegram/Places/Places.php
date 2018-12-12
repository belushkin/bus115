<?php

namespace Bus115\Telegram\Places;

use Silex\Application;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;
use Bus115\Messenger\Messenger;
use Longman\TelegramBot\Commands\UserCommands\LocationCommand;

class Places
{

    private $app;
    private $message;
    private $telegram;

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

    public function setTelegram($telegram)
    {
        $this->telegram = $telegram;
        return $this;
    }

    public function getTelegram()
    {
        return $this->telegram;
    }

    public function text($term)
    {
        $term = $this->app['app.trim_helper']->trim($term);

        if ($this->getMessage()->getLocation()) {
            // If user shared location
            return $this->app['app.telegram.stops']->
            setMessage($this->getMessage())->
            text(
                $this->getMessage()->getLocation()->getLatitude(),
                $this->getMessage()->getLocation()->getLongitude()
            );
        } elseif (empty($term) || strlen($term) < 4) {
            // fallback
            $data = [
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text'    => 'Введіть запит більше 4 символів',
            ];
            return Request::sendMessage($data);
        }

        // Request using WIT.AI NLP provider
        $nlp        = $this->app['app.api']->witai($term);
        $intents    = (isset($nlp['entities']['intent']))   ? $nlp['entities']['intent']    : [];
        $address    = (isset($nlp['entities']['address']))  ? $nlp['entities']['address']   : [];
        $location   = (isset($nlp['entities']['location'])) ? $nlp['entities']['location']  : [];

        if (empty($intents) && empty($address) && empty($location)) {
            $data = [
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
            ];
            return Request::sendMessage($data);
        } else if (!empty($address)) {
            $this->app['monolog']->info("ADDRESS " . $address);
            foreach ($address as $item) {
                // Search through the Eway
                return $this->searchPlaces(urlencode($item['value']));
            }
        } else if (!empty($location)) {
            foreach ($location as $item) {
                try {
                    \Longman\TelegramBot\TelegramLog::debug(sprintf('Telegram Google works'));
                    $results = $this->app['app.api']->getGoogleCoordinates($item['value']);
                } catch (\InvalidArgumentException $e) {
                    $results = [];
                }
                return $this->getStopsByGoogleCoordinates($results);
            }
        } else if (!empty($intents)) {
            foreach ($intents as $intent) {
                if ($intent['value'] == 'joke' && $intent['confidence'] > Messenger::NLP_THRESHOLD) {
                    $data = [
                        'chat_id' => $this->getMessage()->getChat()->getId(),
                        'text'    => 'Прикольно',
                    ];
                    $result = Request::sendMessage($data);
                    if (!$result->isOk()) {
                        $this->app['monolog']->info("Joke ERROR " . $result->getDescription());
                        return Request::emptyResponse();
                    }
                    return $result;
                } else if ($intent['value'] == 'location_ask' && $intent['confidence'] > Messenger::NLP_THRESHOLD) {
                    return $this->getTelegram()->executeCommand('location');
                } else if ($intent['value'] == 'first_hand_shake' && $intent['confidence'] > Messenger::NLP_THRESHOLD) {
                    $data = [
                        'chat_id' => $this->getMessage()->getChat()->getId(),
                        'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
                    ];
                    return Request::sendMessage($data);
                } else {
                    $data = [
                        'chat_id' => $this->getMessage()->getChat()->getId(),
                        'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
                    ];
                    return Request::sendMessage($data);
                }
            }
        }
        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
        ];
        return Request::sendMessage($data);
    }

    private function searchPlaces($term)
    {
        $body = $this->app['app.eway']->getPlacesByName($term);
        if (empty($body->item)) {
            $data = [
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
            ];
            return Request::sendMessage($data);
        }

        $elements   = [];
        $subtitles  = [];
        foreach ($body->item as $item) {
            $button = new InlineKeyboardButton(['text' => 'Обрати', 'callback_data' => $item->id]);
            $keyboard = new InlineKeyboard($button);
            $keyboard->setResizeKeyboard(true);

            $subtitle = $this->app['app.stops']->getStopSubtitle($item->routes);
            if (in_array($subtitle, $subtitles)) {
                continue;
            }
            $elements[] = [
                'chat_id'       =>  intval($this->getMessage()->getChat()->getId()),
                'latitude'      =>  $item->lat,
                'longitude'     =>  $item->lng,
                'title'         =>  $item->title,
                'address'       =>  'Транспорт: ' . $subtitle,
                'reply_markup'  =>  $keyboard
            ];
            $subtitles[] = $subtitle;
        }
        return $this->app['app.telegram.response']->venues($elements);
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
            'text'    => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
        ];
        $result = Request::sendMessage($data);
        if (!$result->isOk()) {
            $this->app['monolog']->info("Places ERROR " . $result->getDescription());
            return Request::emptyResponse();
        }
        return $result;
    }

}
