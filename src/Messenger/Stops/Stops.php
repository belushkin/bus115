<?php

namespace Bus115\Messenger\Stops;

use Silex\Application;

// When user shared location
class Stops implements AttachmentInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    // Work with coordinates from shared location
    public function text(Array $attachment = [])
    {
        $lat        = $attachment['payload']['coordinates']['lat'];
        $lng        = $attachment['payload']['coordinates']['long'];

        $body       = $this->app['app.eway']->getStopsNearPoint($lat, $lng);
        if (isset($body->stop) && is_array($body->stop) && !empty($body->stop)) {
            $elements   = [];
            $responses  = [];
            $i = 0;
            $subtitles  = [];
            foreach ($body->stop as $stop) {
                $subtitle = $this->getStopSubtitle($stop->routes);
                if (in_array($subtitle, $subtitles)) {
                    continue;
                }
                $elements[] = [
                    'title' => $stop->title,
                    'subtitle' => 'Транспорт: ' . $subtitle,
                    'image_url' => $this->app['app.address']->getStopImage($stop->id, $stop->lat, $stop->lng),
                    'buttons' => $this->getMessengerStopButtonsArray($stop->id, $stop->routes)
                ];
                $subtitles[] = $subtitle;
                $i++;
                if ($i % 10 == 0) {
                    $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
                    $elements = [];
                }
            }
            $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
            return $responses;
        }

        // If nothing found through the eway API
        $responses[] = [
            'text' => "Наразі зупинки шукаються в радіусі 400 метрів від визначених координат, надрукуйте назву вулиці, провулку площі або зупинки поруч з Вами.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

    public function getStopSubtitle($routes)
    {
        $result = [];
        if (isset($routes->route) && is_array($routes->route)) {
            foreach ($routes->route as $route) {
                $result[] = $route->title;
            }
            return implode(", ", $result);
        }
        return '';
    }

    public function getMessengerStopButtonsArray($id, $routes)
    {
        if (isset($routes->route) && is_array($routes->route)) {
            $list       = [];
            $buttons    = [];
            foreach ($routes->route as $route) {
                //$this->app['monolog']->info("ROUTE" . $route);
                if (!isset($route->type)) {
                    continue;
                }
                if ($route->type == 'bus') {
                    $list['bus'] = true;
                }
                if ($route->type == 'trol') {
                    $list['trol'] = true;
                }
                if ($route->type == 'tram') {
                    $list['tram'] = true;
                }
                if ($route->type == 'marshrutka') {
                    $list['marshrutka'] = true;
                }
                if ($route->type == 'train') {
                    $list['train'] = true;
                }
                if ($route->type == 'metro') {
                    $list['metro'] = true;
                }
                if ($route->type == 'light-rail') {
                    $list['light-rail'] = true;
                }
            }
            $this->app['monolog']->info("ROUTE 111" );
            foreach (array_keys($list) as $transport) {
                $buttons[] = [
                    'type' => 'postback',
                    'title' => $this->getButtonTitle($transport),
                    'payload' => $id . '_' . $transport
                ];
            }
        }
        $buttons[] = [
            'type' => 'postback',
            'title' => $this->getButtonTitle(false),
            'payload' => $id
        ];
        $this->app['monolog']->info("ROUTE 222" );
        return [$buttons];
    }

    private function getButtonTitle($type)
    {
        if ($type == 'bus') {
            return 'Автобуси';
        }
        if ($type == 'trol') {
            return 'Тролейбуси';
        }
        if ($type == 'tram') {
            return 'Трамваї';
        }
        if ($type == 'marshrutka') {
            return 'Маршрутки';
        }
        if ($type == 'train') {
            return 'Потяги';
        }
        if ($type == 'metro') {
            return 'Метро';
        }
        if ($type == 'light-rail') {
            return 'Електричкі';
        }
        return 'Показати всі';
    }

}
