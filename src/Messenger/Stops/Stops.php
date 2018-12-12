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
                    'buttons' => [
                        [
                            'type' => 'postback',
                            'title' => 'Вибрати цю зупинку',
                            'payload' => $stop->id
                        ]
                    ]
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

}
