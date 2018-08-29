<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class RegularText implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        $this->app['monolog']->info(sprintf('SEARCH WORKS, User entered Term: %s', $term));
        if (!empty($term) && strlen($term) >= 4) {
            $body       = $this->app['app.eway']->getPlacesByName($term);
            if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
                $i = 0;
                $elements   = [];
                $responses  = [];
                foreach ($body->item as $item) {
                    $elements[] = [
                        'title'     => $item->title,
                        'subtitle'  => 'В напрямку ' . $this->getStopDirection($item->id),
                        'image_url' => $this->getStopImage($item->id),
                        'buttons' => [
                            [
                                'type' => 'postback',
                                'title' => 'Вибрати цю зупинку',
                                'payload' => $item->id
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
            } else {
                $results = $this->app['app.api']->getGoogleCoordinates($term);
                return $this->getStopsByGoogleCoordinates($results);
            }

        }

        $responses[] = [
            'text' => "Нічого не знайдено, для допомоги надрукуй help",
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
        $imageUrl = 'https://bus115.kiev.ua/images/stop.jpg';
        $entity = $this->app['em']->getRepository('Bus115\Entity\Stop')->findOneBy(
            array('eway_id' => $id)
        );
        if ($entity) {
            $imageUrl = "https://bus115.kiev.ua/images/stops/{$entity->getName()}";
        }
        return $imageUrl;
    }

    private function getStopsByGoogleCoordinates($results)
    {
        if (isset($results->results[0]->geometry->location)) {
            $location = $results->results[0]->geometry->location;
            $this->app['monolog']->info(sprintf('Google LOCATION, %s', \GuzzleHttp\json_encode($location)));

            $attachment = [
                'payload' => [
                    'coordinates' => [
                        'lat' => $location->lat,
                        'long' => $location->lng
                    ]
                ]
            ];
            return $this->app['app.stops']->text($attachment);
        }

        $responses[] = [
            'text' => "Нічого не знайдено, для допомоги надрукуй help",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        return $responses;
    }

}
