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
        $term = htmlspecialchars(addslashes(trim($term)));
        $this->app['monolog']->info(sprintf('User entered Term: %s', $term));
        if (!empty($term) && strlen($term) > 5) {
            $body       = $this->app['app.eway']->getPlacesByName($term);
            if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
                $i = 0;
                $elements   = [];
                $responses  = [];
                foreach ($body->item as $item) {
                    $elements[] = [
                        'title'     => $item->title,
                        'subtitle'  => 'В напрямку ' . $this->getStopDirection($item->id),
                        'image_url' => "https://bus115.kiev.ua/images/stop.jpg",
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
            }
        }

        $responses[] = [
            'text' => "Нажаль, по запросу: {$term} нічого не знайдено. Спробуйте вказати строку більше 5 символів",
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
}
