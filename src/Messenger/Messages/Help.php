<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class Help implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        $responses[] = [
            'text' => 'Напиши назву вулиці де знаходиться зупинка, або напиши "де я".',
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
