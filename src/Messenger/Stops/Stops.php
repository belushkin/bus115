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
                    'subtitle' => $this->getStopSubtitle($stop->routes),
                    'image_url' => $this->getStopImage($stop->id, $stop->lat, $stop->lng),
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
            return 'Транспорт: ' . implode(", ", $result);
        }
        return '';
    }

    private function getStopImage($id, $lat, $lng)
    {
        $imageUrl = "https://maps.googleapis.com/maps/api/staticmap?center={$lat},{$lng}&zoom=16&size=400x400&maptype=terrain&markers=color:blue%7Clabel:S%7C{$lat},{$lng}&key=" . $this->app['eway']['maps_key'];
        $entity = $this->app['em']->getRepository('Bus115\Entity\Stop')->findOneBy(
            array('eway_id' => $id)
        );
        if ($entity) {
            $imageUrl = "https://bus115.kiev.ua/images/stops/{$entity->getName()}";
        }
        return $imageUrl;
    }

}
