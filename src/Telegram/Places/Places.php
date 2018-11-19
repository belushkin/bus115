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
        //\Longman\TelegramBot\TelegramLog::debug(sprintf('TELEGRAM SEARCH WORKS, User entered Term: %s', $term));
        $term = $this->app['app.regular_text']->stripTerms($term);
        //\Longman\TelegramBot\TelegramLog::debug(sprintf('Term after STRIP: %s', $term));

        if ($this->getMessage()->getLocation()) {
            return $this->app['app.telegram.stops']->
            setMessage($this->getMessage())->
            text(
                $this->getMessage()->getLocation()->getLatitude(),
                $this->getMessage()->getLocation()->getLongitude()
            );
        } elseif (empty($term) || strlen($term) < 4) {
            $data = [
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text'    => 'Введіть запит більше 4 символів',
            ];
            return Request::sendMessage($data);
        }

        // Request using WIT.AI NLP provider
        $term = htmlspecialchars(addslashes(trim(mb_strtolower($term))));
        $nlp        = $this->app['app.api']->witai($term);
        $intents    = (isset($nlp['entities']['intent']))   ? $nlp['entities']['intent']    : [];
        $address    = (isset($nlp['entities']['address']))  ? $nlp['entities']['address']   : [];
        $location   = (isset($nlp['entities']['location'])) ? $nlp['entities']['location']  : [];

        if (empty($intents) && empty($address) && empty($location)) {
            return $this->searchPlaces($term);
        } else if (!empty($location)) {
            foreach ($location as $item) {
                return $this->searchPlaces(
                    $this->app['app.trim_helper']->trim($item['value'])
                );
            }
        } else if (!empty($address)) {
            foreach ($address as $item) {
                return $this->searchPlaces(
                    $this->app['app.trim_helper']->trim($item['value'])
                );
            }
        }  else if (!empty($intents)) {
            foreach ($intents as $intent) {
                if ($intent['value'] == 'joke' && $intent['confidence'] > Messenger::NLP_THRESHOLD) {
                    $data = [
                        'chat_id' => $this->getMessage()->getChat()->getId(),
                        'text'    => 'Прикольно',
                    ];
                    return Request::sendMessage($data);
                } else if ($intent['value'] == 'location_ask' && $intent['confidence'] > Messenger::NLP_THRESHOLD) {
                    $command = new LocationCommand($this->getMessage()->getTelegram());
                    return $command->execute();
                } else if ($intent['value'] == 'first_hand_shake' && $intent['confidence'] > Messenger::NLP_THRESHOLD) {
                    $command = new LocationCommand($this->getMessage()->getTelegram());
                    return $command->execute();
                } else {
                    return $this->searchPlaces($term);
                }
            }
        }
        return $this->searchPlaces($term);
    }

    private function searchPlaces($term)
    {
        $body = $this->app['app.eway']->getPlacesByName($term);

        if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
            $elements  = [];
            foreach ($body->item as $item) {
                $button = new InlineKeyboardButton(['text' => 'Обрати', 'callback_data' => 'stop_' . $item->id]);
                $keyboard = new InlineKeyboard($button);
                $keyboard->setResizeKeyboard(true);

                $direction = $this->app['app.regular_text']->getStopDirection($item->id);
                if ($direction == '-') continue;

                $elements[] = [
                    'chat_id'       =>  $this->getMessage()->getChat()->getId(),
                    'latitude'      =>  $item->lat,
                    'longitude'     =>  $item->lng,
                    'title'         =>  $item->title,
                    'address'       =>  'В напрямку ' . $direction,
                    'reply_markup'  =>  $keyboard
                ];
            }
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
            'text'    => 'Нічого не знайдено, для допомоги надрукуй /help',
        ];
        return Request::sendMessage($data);
    }

}
