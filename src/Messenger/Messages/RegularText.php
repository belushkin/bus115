<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class RegularText implements MessageInterface
{

    private $app;
    private $terms = [
        'ул', 'ул.', 'вул', 'вул.', 'виця',
        'улица', 'улиця', 'уліца', 'остановка', 'житловий комлекс',
        'житловый комплекс', 'станція метро', 'станция метро'
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        $this->app['monolog']->info(sprintf('SEARCH WORKS, User entered Term: %s', $term));
        $term = $this->stripTerms(mb_strtolower($term));
        $this->app['monolog']->info(sprintf('Term after STRIP: %s', $term));

        if (!empty($term) && strlen($term) >= 4) {
            $body       = $this->app['app.eway']->getPlacesByName($term);
            if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
                $i = 0;
                $elements   = [];
                $responses  = [];
                $subtitles  = [];
                foreach ($body->item as $item) {
                    $subtitle = $this->app['app.stops']->getStopSubtitle($item->routes);
                    if (in_array($subtitle, $subtitles)) {
                        continue;
                    }
                    $elements[] = [
                        'title'     => $item->title,
                        'subtitle'  => 'Транспорт: ' . $subtitle,
                        'image_url' => $this->getStopImage($item->id, $item->lat, $item->lng),
                        'buttons' => [
                            [
                                'type' => 'postback',
                                'title' => 'Вибрати цю зупинку',
                                'payload' => $item->id
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
            } else {
                try {
                    $results = $this->app['app.api']->getGoogleCoordinates($term);
                } catch (\InvalidArgumentException $e) {
                    $results = [];
                }
                return $this->getStopsByGoogleCoordinates($results);
            }

        }

        $responses[] = [
            'text' => "Перевірте правильність написання або скористайтеся функцією location",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

    public function getStopImage($id, $lat, $lng)
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
            'text' => "Перевірте правильність написання або скористайтеся функцією location",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        return $responses;
    }

    public function stripTerms($term)
    {
        return trim(preg_replace('/\s+/', ' ',str_replace($this->terms,'', $term)));
    }
}
