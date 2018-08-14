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
            'text' => 'Напиши просто назву вулиці, або провулку, або площі, або на пиши "де я" і я зрозумію, що ти хочеш.',
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
