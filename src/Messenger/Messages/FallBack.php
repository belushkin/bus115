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
            'text' => 'Перевірте правильність написання або скористайтеся функцією location',
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
