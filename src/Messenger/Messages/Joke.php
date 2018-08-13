<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class Joke implements MessageInterface
{

    private $app;
    private $jokes = [
        'Дуже розумно',
        'Гарний жарт',
        'Хм',
        'Весело',
        'Прикольно',
        'Навзаєм'
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        $responses[] = [
            'text' => rand(0, count($this->jokes)),
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
