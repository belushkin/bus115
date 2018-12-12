<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class AskLocation implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        $responses[] = [
            'text' => 'Надрукуйте назву вулиці, провулку площі або зупинки, або скористайтеся функцією location',
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
