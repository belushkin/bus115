<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\HelpCommand;
use Longman\TelegramBot\Commands\UserCommands\WhoamiCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Location;
use Longman\TelegramBot\Entities\Venue;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class GenericmessageCommand extends SystemCommand
{
    protected $name = 'Genericmessage';
    protected $description = 'Handle generic message';
    protected $version = '1.1.0';
    protected $need_mysql = false;

    public function executeNoDb()
    {
        return Request::emptyResponse();
    }

    public function execute()
    {
//
//        $data = [
//            'chat_id'       => $this->getMessage()->getChat()->getId(),
//            'latitude'      =>  '50.44033432006836',
//            'longitude'     =>  '30.619548797607422',
//            'title'         => 'Venue title',
//            'address'       => 'Venue address',
//            'reply_markup'  => $keyboard,
//        ];
//
//        Request::sendVenue($data);
//        Request::sendVenue($data);
//        return true;


//        $update = json_decode($this->update->toJson(), true);

        $term = trim($this->getMessage()->getText(true));

        \Longman\TelegramBot\TelegramLog::debug(sprintf('TELEGRAM SEARCH WORKS, User entered Term: %s', $term));
        $term = $this->telegram->app['app.regular_text']->stripTerms($term);
        \Longman\TelegramBot\TelegramLog::debug(sprintf('Term after STRIP: %s', $term));

        if (empty($term) || strlen($term) < 4) {
            $data = [
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text'    => 'Введіть запит більше 4 символів',
            ];
            return Request::sendMessage($data);
        }

        $body = $this->telegram->app['app.eway']->getPlacesByName($term);

        if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
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
                    'address'       =>  'В напрямку ' . $this->telegram->app['app.regular_text']->getStopDirection($item->id),
                    'reply_markup'  =>  $keyboard
                ];
            }
        } else {
            try {
                \Longman\TelegramBot\TelegramLog::debug(sprintf('Telegram Google works'));
                $results = $this->telegram->app['app.api']->getGoogleCoordinates($term);
            } catch (\InvalidArgumentException $e) {
                $results = [];
            }
            return $this->getStopsByGoogleCoordinates($results);
        }

//
//        if ($text === 'Need some help') {
//            $update['message']['text'] = '/help';
//            return (new HelpCommand($this->telegram, new Update($update)))->preExecute();
//        }
//        if ($text === 'Who am I?') {
//            $update['message']['text'] = '/whoami';
//            return (new WhoamiCommand($this->telegram, new Update($update)))->preExecute();
//        }
//
//        return Request::emptyResponse();
    }


    private function getStopsByGoogleCoordinates($results)
    {
        if (isset($results->results[0]->geometry->location)) {
            $location = $results->results[0]->geometry->location;
            \Longman\TelegramBot\TelegramLog::debug(sprintf('Google LOCATION, %s', \GuzzleHttp\json_encode($location)));

            return $this->telegram->app['app.telegram.stops']->text($location->lat, $location->lng);
        }

        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'text'    => 'Нічого не знайдено, для допомоги надрукуй help',
        ];
        return Request::sendMessage($data);
    }

}
