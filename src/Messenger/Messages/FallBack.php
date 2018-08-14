<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class FallBack implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        $responses[] = [
            'text' => 'Не розумію :(, але я дуже швидко навчаюсь, обіцяю, що я виправлюсь незабаром',
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
