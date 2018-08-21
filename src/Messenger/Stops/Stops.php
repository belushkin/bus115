<?php

namespace Bus115\Messenger\Stops;

use Silex\Application;
use Bus115\Entity\Stop;

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
                    'subtitle' => 'В напрямку ' . $this->getStopDirection($stop->id),
                    'image_url' => $this->getStopImage($stop->id),
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

    private function getStopDirection($id)
    {
        $body = $this->app['app.eway']->handleStopInfo($id);
        if (isset($body->routes) && is_array($body->routes) && !empty($body->routes)) {
            return $body->routes[0]->directionTitle . ', (' . $body->routes[0]->transportName . ')';
        }
        return '-';
    }

    private function getStopImage($id)
    {
//        $this->app['monolog']->info(sprintf('StopId: %s', $id));
        $imageUrl = 'https://bus115.kiev.ua/images/stop.jpg';
        $entity = $this->app['em']->getRepository('Bus115\Entity\Stop')->findOneBy(
            array('eway_id' => $id)
        );
//        $this->app['monolog']->info(sprintf('Entity exists: %s', var_export((array)$entity, true)));
        if ($entity) {
//            $this->app['monolog']->info(sprintf('Entity url: %s', $entity->getUuid()));
            $imageUrl = "https://bus115.kiev.ua/images/stops/{$entity->getUuid()}.jpg";
        }
        return $imageUrl;
    }

}
