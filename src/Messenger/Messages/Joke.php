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
            'text' => $this->jokes[rand(0, count($this->jokes)-1)],
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
