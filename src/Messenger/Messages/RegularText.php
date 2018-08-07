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
        if (!empty($term) && strlen(trim($term)) > 5) {
            $body       = $this->app['app.eway']->getPlacesByName($term);
            if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
                $i = 0;
                $elements   = [];
                $responses  = [];
                foreach ($body->item as $item) {
                    $elements[] = [
                        'title'     => $item->title,
                        'subtitle'  => 'В напрямку:',
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
            'text' => "Нажаль, по запросу: ".htmlspecialchars(stripslashes($term))." нічого не знайдено. Спробуйте вказати строку більше 5 символів",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
