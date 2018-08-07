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
                $stopTransport = $this->callStopTransport($stop->id);
                $elements[] = [
                    'title' => $stop->title,
                    'subtitle' => ($stopTransport) ? $stopTransport->name : '-',
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

    private function callStopTransport($id)
    {
        $body = $this->app['app.eway']->handleStopInfo($id);
        $this->app['monolog']->info(var_export($body->transports, true));
        $this->app['monolog']->info(var_export($body->transports->transport, true));
        if (isset($body->transports) && isset($body->transports->transport)) {
            return $body->transports->transport;
        }
        return null;
    }

}
