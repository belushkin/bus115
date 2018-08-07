<?php

namespace Bus115\Messenger\Stops;

use Silex\Application;

class Stops implements AttachmentInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text(Array $attachment = [])
    {
        $lat        = $attachment['payload']['coordinates']['lat'];
        $lng        = $attachment['payload']['coordinates']['long'];

        $body       = $this->app['app.eway']->getStopsNearPoint($lat, $lng);
        if (isset($body->stop) && is_array($body->stop) && !empty($body->stop)) {
            $elements   = [];
            $responses  = [];
            $i = 0;
            foreach ($body->stop as $stop) {
                $elements[] = [
                    'title' => $stop->title,
                    'image_url' => 'https://bus115.kiev.ua/images/stop.jpg',
                    'buttons' => [
                        [
                            'type' => 'postback',
                            'title' => 'Вибрати цю зупинку',
                            'payload' => $stop->id
                        ]
                    ]
                ];
                $i++;
                if ($i % 10 == 0) {
                    $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
                    $elements = [];
                }
            }
            $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
            return $responses;
        }

        $responses[] = [
            'text' => "Нажаль, нічого не знайдено.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
