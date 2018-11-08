<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\HelpCommand;
use Longman\TelegramBot\Commands\UserCommands\WhoamiCommand;
use Longman\TelegramBot\Entities\Update;
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
        return Request::emptyResponse();
        $update = json_decode($this->update->toJson(), true);

        $term = trim($this->getMessage()->getText(true));

        \Longman\TelegramBot\TelegramLog::debug(sprintf('TELEGRAM SEARCH WORKS, User entered Term: %s', $term));
        $term = $this->telegram->app['app.regular_text']->stripTerms($term);
        \Longman\TelegramBot\TelegramLog::debug(sprintf('Term after STRIP: %s', $term));

        if (!empty($term) && strlen($term) >= 4) {
            $body       = $this->telegram->app['app.eway']->getPlacesByName($term);
            if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
                $i = 0;
                $elements   = [];
                $responses  = [];
                foreach ($body->item as $item) {
                    $elements[] = [
                        'title'     => $item->title,
                        'subtitle'  => 'В напрямку ' . $this->telegram->app['app.regular_text']->getStopDirection($item->id),
                        'image_url' => $this->telegram->app['app.regular_text']->getStopImage($item->id),
//                        'buttons' => [
//                            [
//                                'type' => 'postback',
//                                'title' => 'Вибрати цю зупинку',
//                                'payload' => $item->id
//                            ]
//                        ]
                    ];
                    $i++;
                    if ($i % 10 == 0) {
                        $responses[] = $this->telegram->app['app.telegram.response']->generateGenericResponse($elements);
                        $elements = [];
                    }
                }
                $responses[] = $this->telegram->app['app.telegram.response']->generateGenericResponse($elements);
                return $responses;
            } else {
                try {
                    $results = $this->telegram->app['app.api']->getGoogleCoordinates($term);
                } catch (\InvalidArgumentException $e) {
                    $results = [];
                }
                return $this->getStopsByGoogleCoordinates($results);
            }

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
//        if (isset($results->results[0]->geometry->location)) {
//            $location = $results->results[0]->geometry->location;
//            $this->app['monolog']->info(sprintf('Google LOCATION, %s', \GuzzleHttp\json_encode($location)));
//
//            $attachment = [
//                'payload' => [
//                    'coordinates' => [
//                        'lat' => $location->lat,
//                        'long' => $location->lng
//                    ]
//                ]
//            ];
//            return $this->app['app.stops']->text($attachment);
//        }
//
//        $responses[] = [
//            'text' => "Нічого не знайдено, для допомоги надрукуй help",
//            'quick_replies' => [
//                [
//                    'content_type' => 'location',
//
//                ]
//            ]
//        ];
//        return $responses;
        return [];
    }

}
